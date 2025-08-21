<?php
namespace Utphpcore\Core;

class AuthenticationToken
{
    /**
     * @var int
     */
    private int $userId;
    
    /**
     * @var array
     */
    private array $instanceIds;
    
    /**
     * @var bool
     */
    private bool $isAdministrator;
    
    /**
     * @param int $userId
     * @param array $instanceIds
     * @param bool $isAdministrator
     */
    function __construct(int $userId, array $instanceIds, bool $isAdministrator)
    {
        $this -> instanceIds = $instanceIds;
        $this -> userId = $userId;
        $this -> isAdministrator = $isAdministrator;
    }
    
    /**
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        $isAuthenticated = false;
        $currentInstanceId = \Utphpcore\Data\Cache::get(\Utphpcore\Core::InstanceID);
        if(in_array($currentInstanceId, $this -> instanceIds) || $this -> isAdministrator)
        {
            $isAuthenticated = true;
        }
        return $isAuthenticated;
    }
    
    /**
     * @return bool
     */
    public function isAdministrator(): bool
    {
        return $this -> isAdministrator;
    }
    
    /**
     * @return int
     */
    public function userId(): int
    {
        return $this -> userId;
    }
}