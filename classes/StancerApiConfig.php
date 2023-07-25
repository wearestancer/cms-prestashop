<?php
/**
 * Stancer PrestaShop
 *
 * @author    Stancer <hello@stancer.com>
 * @copyright 2018-2023 Stancer / Iliad 78
 * @license   https://opensource.org/licenses/MIT
 *
 * @website   https://www.stancer.com
 *
 * @version   1.2.0
 */

/**
 * Configuration helper.
 */
class StancerApiConfig
{
    /** @var string Mode Live or Test */
    public $mode;

    /** @var string API Host */
    public $host;

    /** @var int|null API Timeout */
    public $timeout;

    /** @var string Auth limit */
    public $authLimit;

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

        return $apiConfig
            ->addAppData('libstancer-prestashop', Stancer::VERSION)
            ->addAppData('prestashop', _PS_VERSION_)
        ;
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
     * Checks if on live mode
     *
     * @return bool
     */
    public function isLiveMode(): bool
    {
        return $this->mode === Stancer\Config::LIVE_MODE;
    }

    /**
     * Checks if on test mode
     *
     * @return bool
     */
    public function isTestMode(): bool
    {
        return $this->mode === Stancer\Config::TEST_MODE;
    }

    /**
     * Checks if is configured
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        $apiConfig = $this->getConfig();

        if (empty($apiConfig->getPublicKey())) {
            return false;
        }

        if (empty($apiConfig->getSecretKey())) {
            return false;
        }

        return true;
    }
}
