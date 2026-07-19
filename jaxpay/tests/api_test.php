<?php
// Simple API test for JAXPAY OTP endpoints
// Run: php tests/api_test.php

$base = __DIR__ . '/../';
$kirim = $base . 'proses/kirim_otp.php';
$verify = $base . 'proses/verify_otp.php';

function post($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HEADER, false);
    // follow redirects
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) {
        throw new Exception('Curl error: ' . $err);
    }
    $json = json_decode($res, true);
    return [$res, $json];
}

try {
    echo "\n== JAXPAY API OTP Test ==\n";
    // Make a test email (one of demo emails)
    $email = 'ahmad@student.jaxpay.id';
    echo "Posting to kirim_otp.php with email=$email...\n";
    [$raw, $data] = post($kirim, ['email' => $email]);
    echo "Raw response:\n" . $raw . "\n";
    if (!is_array($data)) throw new Exception('Response is not JSON');
    if (!($data['success'] ?? false)) {
        throw new Exception('kirim_otp failed: ' . ($data['message'] ?? 'no message'));
    }
    echo "kirim_otp returned success.\n";
    $demo = $data['demo_otp'] ?? null;
    if ($demo) {
        echo "Demo OTP present: $demo\n";
        echo "Posting to verify_otp.php...\n";
        [$raw2, $data2] = post($verify, ['email' => $email, 'kode' => $demo]);
        echo "Raw response:\n" . $raw2 . "\n";
        if (!is_array($data2)) throw new Exception('verify response not JSON');
        if (!($data2['success'] ?? false)) {
            throw new Exception('verify_otp failed: ' . ($data2['message'] ?? 'no message'));
        }
        echo "verify_otp succeeded: Welcome " . ($data2['nama'] ?? '(no name)') . "\n";
    } else {
        echo "No demo_otp present. If email sending configured, check the email inbox for OTP.\n";
    }
    echo "\nAll done.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
