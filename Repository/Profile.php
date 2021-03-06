<?php

namespace ThemeHouse\InstallAndUpgrade\Repository;

use ThemeHouse\InstallAndUpgrade\InstallAndUpgrade\AbstractHandler;
use XF\Mvc\Entity\Repository;

class Profile extends Repository
{
    protected $handlers;

    /**
     * @throws \Exception
     */
    public function getHandlers()
    {
        $contentTypes = $this->app()->getContentTypeField('th_installupgrade_handler');

        foreach ($contentTypes as $contentType => $class) {
            if (!isset($this->handlers[$contentType])) {
                $classString = $this->app()->extendClass($class);
                $this->handlers[$contentType] = new $classString();
            }
        }

        return $this->handlers;
    }

    /**
     * @param $key
     * @return AbstractHandler
     * @throws \Exception
     */
    public function getHandler($key)
    {
        if (!isset($this->handlers)) {
            $this->getHandlers();
        }

        return $this->handlers[$key];
    }

    /**
     * @return \XF\Mvc\Entity\Finder
     */
    public function findProfiles()
    {
        return $this->finder('ThemeHouse\InstallAndUpgrade:Profile');
    }

    /**
     * @return \XF\Mvc\Entity\ArrayCollection
     */
    public function getProductListProfiles()
    {
        $profiles = $this->findProfiles()->fetch();

        foreach ($profiles as $key => $profile) {
            /** @var \ThemeHouse\InstallAndUpgrade\Entity\Profile $profile */
            try {
                /** @var AbstractHandler $handler */
                $handler = $profile->getHandler();
            } catch (\Exception $e) {
                $handler = null;
            }

            if (!$handler || !$handler->getCapability('productList')) {
                $profiles->offsetUnset($key);
                continue;
            }
        }

        return $profiles;
    }
}