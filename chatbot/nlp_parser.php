<?php

require_once 'config.php';

const INTENT_ADD     = 'add';
const INTENT_UPDATE  = 'update';
const INTENT_DELETE  = 'delete';
const INTENT_LIST    = 'list';
const INTENT_UNKNOWN = 'unknown';

function parse_intent(string $text): array
{
    return parse_with_rules($text);
}

function parse_with_rules(string $text): array
{
    $lower        = strtolower(trim($text));
    $emailPattern = '/[\w\.\-]+@[\w\.\-]+\.\w+/';
    $fieldPattern = '(?:address|city|phone|number|name)';

    /* ---------- DELETE ---------- */
    if (preg_match('/\b(remove|delete)\b/', $lower)) {
        if (preg_match($emailPattern, $text, $m)) {
            return ['action' => INTENT_DELETE, 'target' => $m[0]];
        }
        if (preg_match('/\b(?:remove|delete)\s+(?:the\s+)?(?:user\s+)?([\w\.@]+)/i', $text, $m)) {
            return ['action' => INTENT_DELETE, 'target' => $m[1]];
        }
        return ['action' => INTENT_UNKNOWN];
    }

    /* ---------- LIST ---------- */
    if (preg_match('/\b(list|show|display|all users|everyone)\b/', $lower)) {
        return ['action' => INTENT_LIST];
    }

    /* ---------- SET / UPDATE ---------- */
    if (preg_match('/\b(update|change|set|modify|edit)\b/', $lower)) {

        if (preg_match(
            "/\b(?:update|change|set|modify|edit)\s+([\w\.@]+)(?:'s)?\s+(?:the\s+)?(\w+)\s+(?:to|as|into)\s+(.+)$/i",
            $text, $m
        )) {
            return [
                'action' => INTENT_UPDATE,
                'target' => trim($m[1]),
                'field'  => normalize_field($m[2]),
                'value'  => trim($m[3]),
            ];
        }

        if (preg_match(
            "/\b(?:update|change|set|modify|edit)\s+(?:the\s+)?(" . $fieldPattern . ")\s+(?:to|as|into)\s+(.+?)\s+for\s+([\w\.@]+)/i",
            $text, $m
        )) {
            return [
                'action' => INTENT_UPDATE,
                'target' => trim($m[3]),
                'field'  => normalize_field($m[1]),
                'value'  => trim($m[2]),
            ];
        }

        return ['action' => INTENT_UNKNOWN];
    }

    /* ---------- ADD ---------- */
    if (preg_match('/\b(add|create|register)\b/', $lower)) {

        if (preg_match(
            "/\b(?:add|create|register)\s+([\w\.@]+)'s\s+(" . $fieldPattern . ")\s+(?:is\s+)?(.+)$/i",
            $text, $m
        )) {
            return [
                'action' => INTENT_UPDATE,
                'target' => trim($m[1]),
                'field'  => normalize_field($m[2]),
                'value'  => trim($m[3]),
            ];
        }

        if (preg_match(
            "/\b(?:add|create|register)\s+(?:the\s+)?(" . $fieldPattern . ")\s+(?:is\s+)?(.+)$/i",
            $text, $m
        )) {
            return [
                'action' => INTENT_UPDATE,
                'target' => '',
                'field'  => normalize_field($m[1]),
                'value'  => trim($m[2]),
            ];
        }

        $email = preg_match($emailPattern, $text, $m) ? $m[0] : null;

        $name = null;
        if (preg_match('/\b(?:named?|called?)\s+([a-zA-Z][a-zA-Z\s]{1,60})/i', $text, $m)) {
            $name = clean_name($m[1]);
        } elseif (preg_match('/\b(?:add|create|register)\s+(?:the\s+)?(?:user\s+|new user\s+)?([a-zA-Z][a-zA-Z\s]{1,40})/i', $text, $m)) {
            $candidate = clean_name($m[1]);
            if (!in_array(strtolower($candidate), ['user','address','phone','email','number','city','name','a user','the user'], true)
                && !preg_match('/\b(email|phone|number|address|city)\b/i', $candidate)) {
                $name = $candidate;
            }
        }

        $phone = null;
        $phoneSource = $email ? str_replace($email, '', $text) : $text;
        if (preg_match('/\+?\d[\d\s\-]{3,}/', $phoneSource, $m)) {
            $phone = trim($m[0]);
        }

        $city = null;
        if (preg_match('/\b(?:city|in)\s+([a-zA-Z][a-zA-Z\s]{1,40})/i', $text, $m)) {
            $city = clean_name($m[1]);
        }

        return [
            'action' => INTENT_ADD,
            'email'  => $email,
            'name'   => $name,
            'phone'  => $phone,
            'city'   => $city,
        ];
    }

    return ['action' => INTENT_UNKNOWN];
}

function normalize_field(string $field): string
{
    $field = strtolower(trim($field));
    return match ($field) {
        'address'               => 'city',
        'number', 'phonenumber' => 'phone',
        default                 => $field,
    };
}

function clean_name(string $name): string
{
    $name = trim($name);
    $name = preg_split('/\s+(?:and|with|phone|number|email|city|address|at|to)\s+/i', $name)[0];
    $name = preg_replace('/[^a-zA-Z\s]/', '', $name);
    return ucwords(strtolower(trim($name)));
}