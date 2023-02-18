<?php
/**
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2023 Iliad 78
 * @license   https://opensource.org/licenses/MIT
 * @website   https://www.stancer.com
 * @version   1.0.0
 */
class StancerApiConfig
{
    /** @var string Mode Live or Test */
    public $mode;

    /** @var string API Host */
    public $host;

    /** @var string API Timeout */
    public $timeout;

    /** @var string Auth limit */
    public $authLimit;

    /** @var string Public API key */
    public $publicKey;

    /** @var string Secret API key */
    public $secretKey;

    /** @var bool API is configured ? */
    public $isConfigured;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->mode = Configuration::get('STANCER_API_MODE') ?: Stancer\Config::TEST_MODE;
        $this->host = Configuration::get('STANCER_API_HOST');
        $this->timeout = Configuration::get('STANCER_API_TIMEOUT');
        $this->authLimit = Configuration::get('STANCER_AUTH_LIMIT');
        $this->publicKey = $this->getPublicKey();
        $this->secretKey = $this->getSecretKey();
        $this->isConfigured = $this->isConfigured();
    }

    /**
     * Get API configuration
     *
     * @return Stancer\Config
     */
    private function getConfig(): Stancer\Config
    {
        $apiConfig = Stancer\Config::init([
            $this->getPublicKey(),
            $this->getSecretKey(),
        ]);

        $apiConfig->setMode($this->mode);

        if ($this->host) {
            $apiConfig->setHost($this->host);
        }

        if ($this->timeout) {
            $apiConfig->setTimeout($this->timeout);
        }

        return $apiConfig;
    }

    /**
     * Get the public API key
     *
     * @return string
     */
    private function getPublicKey(): string
    {
        if ($this->isTestMode()) {
            return Configuration::get('STANCER_API_TEST_PUBLIC_KEY');
        }

        return Configuration::get('STANCER_API_LIVE_PUBLIC_KEY');
    }

    /**
     * Get the secrect API key
     *
     * @return string
     */
    private function getSecretKey(): string
    {
        if ($this->isTestMode()) {
            return Configuration::get('STANCER_API_TEST_SECRET_KEY');
        }

        return Configuration::get('STANCER_API_LIVE_SECRET_KEY');
    }

    /**
     * Checks if on test mode
     *
     * @return bool
     */
    public function isTestMode(): bool
    {
        if ($this->mode === Stancer\Config::TEST_MODE) {
            return true;
        }

        return false;
    }

    /**
     * Checks if is configured
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        $apiConfig = $this->getConfig();

        if (
            !empty($apiConfig)
            && !empty($apiConfig->getPublicKey())
            && !empty($apiConfig->getSecretKey())
        ) {
            return true;
        }

        return false;
    }
}
