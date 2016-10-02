<?php
namespace Services\Security\Csrf;


class CsrfGenerator {
    
     /**
     * The amount of entropy collected for each token (in bits).
     *
     * @var int
     */
    private $entropy;

    /**
     * Generates URI-safe CSRF tokens.
     *
     * @param int $entropy The amount of entropy collected for each token (in bits)
     */
    public function __construct($entropy = 256)
    {
        $this->entropy = $entropy;
    }

    /**
     * {@inheritdoc}
     */
    public function generateToken()
    {
        // Generate an URI safe base64 encoded string that does not contain "+",
        // "/" or "=" which need to be URL encoded and make URLs unnecessarily
        // longer.
        $bytes = random_bytes($this->entropy / 8);

        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }
}
