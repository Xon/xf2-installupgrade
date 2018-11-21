<?php

namespace ThemeHouse\InstallAndUpgrade\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * Class Profile
 * @package ThemeHouse\InstallAndUpgrade\Entity
 *
 * @property integer profile_id
 * @property string provider_id
 * @property string page_title
 * @property string base_url
 * @property array options
 * @property boolean has_tfa
 *
 * @property Provider Provider
 */
class Profile extends Entity
{
    protected $secret;
    protected $credentials;

    /**
     * @return mixed
     */
    public function getProductsFromProvider()
    {
        $provider = $this->Provider;

        if (!$provider) {
            return null;
        }

        $handler = $provider->handler;

        if (!$handler) {
            return null;
        }

        if ($this->secret) {
            $handler->setEncryptionSecret($this->secret);
        }
        return $handler->getProductsFromProvider($this);
    }

    protected function _postDelete()
    {
        \XF::app()->db()->delete('xf_th_installupgrade_product', 'profile_id = ?', [$this->profile_id]);
        parent::_postDelete();
    }

    /**
     * @return null|\ThemeHouse\InstallAndUpgrade\InstallAndUpgrade\AbstractHandler
     * @throws \Exception
     */
    public function getHandler()
    {
        return $this->Provider->getHandler();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getCredentials()
    {
        if (!$this->credentials) {
            $handler = $this->getHandler();
            $handler->setEncryptionSecret($this->secret);
            $this->credentials = $handler->decryptCredentials($this->options);
        }

        return $this->credentials;
    }

    public function setEncryptionSecret($secret)
    {
        $this->secret = $secret;
    }

    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_th_installupgrade_profile';
        $structure->shortName = 'ThemeHouse\InstallAndUpgrade:Profile';
        $structure->primaryKey = 'profile_id';
        $structure->columns = [
            'profile_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
            'provider_id' => ['type' => self::STR, 'maxLength' => 25, 'required' => true],
            'page_title' => [
                'type' => self::STR,
                'maxLength' => 100,
                'required' => 'please_enter_valid_title'
            ],
            'has_tfa' => ['type' => self::BOOL, 'default' => 0],
            'base_url' => ['type' => self::STR, 'maxLength' => 100, 'default' => ''],
            'options' => ['type' => self::JSON_ARRAY, 'default' => []],
            'active' => ['type' => self::BOOL, 'default' => 1],
            'requires_decryption' => ['type' => self::BOOL, 'default' => 0],
        ];
        $structure->getters = [];
        $structure->relations = [
            'Provider' => [
                'entity' => 'ThemeHouse\InstallAndUpgrade:Provider',
                'type' => self::TO_ONE,
                'conditions' => 'provider_id',
                'primary' => true
            ]
        ];
        $structure->defaultWith = 'Provider';

        return $structure;
    }
}