<?php

class CsrfTokenUtil {
    private const TOKEN_LENGTH = 32; // Length of the random token
    private const TOKEN_EXPIRATION_TIME = 3600; // Token expiration time in seconds (1 hour)
    private const SESSION_TOKEN_KEY = 'csrf_token';
    private const SESSION_EXPIRATION_KEY = 'csrf_token_expiration';

    /**
     * Generates a new CSRF token and stores it in the session.
     */
    public static function generateToken() {
        if (!isset($_SESSION)) {
            session_start();
        }

        // Generate and store the CSRF token and expiration time
        $_SESSION[self::SESSION_TOKEN_KEY] = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION[self::SESSION_EXPIRATION_KEY] = time() + self::TOKEN_EXPIRATION_TIME;
    }

    /**
     * Retrieves the current CSRF token from the session, generating a new one if it doesn't exist or has expired.
     *
     * @return string The CSRF token
     */
    public static function getToken() {
        if (!isset($_SESSION)) {
            session_start();
        }

        // Generate a new token if it doesn't exist or has expired
        if (!isset($_SESSION[self::SESSION_TOKEN_KEY], $_SESSION[self::SESSION_EXPIRATION_KEY])
            || $_SESSION[self::SESSION_EXPIRATION_KEY] < time()) {
            self::generateToken();
        }

        return $_SESSION[self::SESSION_TOKEN_KEY];
    }

    /**
     * Validates a provided CSRF token against the stored token.
     *
     * @param string $token The CSRF token to validate
     * @return bool True if the token is valid, false otherwise
     */
    public static function validateToken($token) {
        if (!isset($_SESSION)) {
            session_start();
        }

        return isset($_SESSION[self::SESSION_TOKEN_KEY], $_SESSION[self::SESSION_EXPIRATION_KEY]) &&
               $_SESSION[self::SESSION_TOKEN_KEY] === $token &&
               $_SESSION[self::SESSION_EXPIRATION_KEY] >= time();
    }

    /**
     * Adds the CSRF token as a meta tag in the HTML head.
     */
    public static function addTokenMetaTag() {
        $token = self::getToken();
        echo '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
    }
}