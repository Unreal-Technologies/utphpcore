<?php
namespace Utphpcore\Core;

class Modalresult
{
    /**
     * @var bool
     */
    private bool $success;
    
    /**
     * @var string
     */
    private string $message;
    
    /**
     * @var bool
     */
    private bool $reload = false;
    
    /**
     * @param bool $success
     */
    public function __construct(bool $success, string $message)
    {
        $this -> success = $success;
        $this -> message = $message;
    }
    
    public function reload(): void
    {
        $this -> reload = true;
    }
    
    /**
     * @return string
     */
    public function __toString(): string 
    {
        $ob = ob_get_clean();
        $result = [
            'output-buffer' => $ob,
            'success' => $this -> success,
            'message' => $this -> message,
            'reload' => $this -> reload
        ];
        
        return json_encode($result);
    }
}