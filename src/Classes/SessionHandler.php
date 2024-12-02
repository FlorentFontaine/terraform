<?php

namespace Classes;

/**
 * The Session class handles session management and flash messages.
 */
class SessionHandler
{
    protected const FLASH_KEY = 'flash_messages';

    /**
     * Constructs a new instance of the class.
     *
     * This method initializes the session by starting it if it has not been started already.
     * It also sets a flag to remove all flash messages after they have been displayed.
     */
    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $flashMessages = $this->getFlashMessages();

        foreach ($flashMessages as &$flashMessage) {
            $flashMessage['remove'] = true;
        }

        $this->setFlashMessages($flashMessages);
    }

    /**
     * The __destruct() magic method deletes flash messages marked for removal from the session.
     *
     * This method retrieves the flash messages using the getFlashMessages() method,
     * iterates through each flash message, and if the 'remove' flag is set to true,
     * removes the flash message from the array using the unset() function.
     *
     * Finally, the updated flash messages array is stored back into the session
     * using the setFlashMessages() method.
     */
    public function __destruct()
    {
        $flashMessages = $this->getFlashMessages();

        foreach ($flashMessages as $key => &$flashMessage) {
            if ($flashMessage['remove']) {
                unset($flashMessages[$key]);
            }
        }

        $this->setFlashMessages($flashMessages);
    }

    /**
     * Sets a flash message in the session.
     *
     * @param string $key The key of the flash message.
     * @param string $message The content of the flash message.
     * @return void
     */
    public function setFlash(string $key, string $message)
    {
        $_SESSION[self::FLASH_KEY][$key] = [
            'remove' => false,
            'value' => $message
        ];
    }

    /**
     * Get the value of a flash message from session
     *
     * @param string $key The key of the flash message to retrieve
     * @return mixed The value of the flash message, or false if not found
     */
    public function getFlash(string $key)
    {
        return $_SESSION[self::FLASH_KEY][$key]['value'] ?? false;
    }

    /**
     * Sets a value in the session using nested keys
     *
     * Example : $session->set(['level1', 'level2', 'level3'], $value);
     * Result : $_SESSION['level1']['level2']['level3'] = $value;
     *
     * @param array $keys The array of keys to traverse the session array
     * @param mixed $value The value to be set in the nested array
     * @return void
     */
    public function set(array $keys, $value)
    {
        $session =& $_SESSION;

        foreach ($keys as $key) {
            if (!isset($session[$key])) {
                $session[$key] = array();
            }

            $session =& $session[$key];
        }

        $session = $value;
    }

    /**
     * Get the value from the session based on the provided keys array
     *
     * @param array $keys The keys array to traverse the session data
     * @return mixed|false The value from the session if found, otherwise returns false
     */
    public function get(array $keys)
    {
        $temp = &$_SESSION;

        foreach ($keys as $key) {
            if (!isset($temp[$key])) {
                return false;
            }

            $temp = &$temp[$key];
        }
        return $temp;
    }

    /**
     * Removes a nested array element specified by a list of keys from the $_SESSION variable.
     *
     * @param array $keys The list of keys specifying the nested array element to remove.
     * @return bool Returns true if the nested array element is successfully removed, or false if it does not exist.
     */
    public function remove(array $keys): bool
    {
        $temp = &$_SESSION;

        $lastKeyIndex = count($keys) - 1;

        foreach($keys as $index => $key) {
            if (!isset($temp[$key])) {
                return false;
            }

            if($index === $lastKeyIndex) {
                unset($temp[$key]);
            } else {
                $temp = &$temp[$key];
            }
        }

        return true;
    }

    /**
     * Retrieves the flash messages stored in the $_SESSION variable under the specified FLASH_KEY.
     *
     * @return array An array of flash messages.
     */
    private function getFlashMessages(): array
    {
        return $_SESSION[self::FLASH_KEY] ?? [];
    }

    /**
     * Sets the flash messages in the $_SESSION variable.
     *
     * @param array $flashMessages The flash messages to be set.
     */
    private function setFlashMessages(array $flashMessages)
    {
        $_SESSION[self::FLASH_KEY] = $flashMessages;
    }
}
