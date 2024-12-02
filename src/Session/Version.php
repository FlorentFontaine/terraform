<?php

namespace Session;

use Classes\SessionHandler;

/**
 * Class VersionLoader
 *
 * This class is responsible for loading and storing the version and version date of the
 * MYREPORT application from the composer.json file.
 */
class Version
{
    private SessionHandler $session;

    public function __construct(SessionHandler $session) {
        $this->session = $session;
    }

    public function run() {
        $composer = json_decode(file_get_contents(__DIR__ . "/../composer.json"), true);

        $version = array_key_exists("version", $composer) ? $composer["version"] : "";
        $this->session->set(["MYREPORT_VERSION"], $version);

        $versionDateRelease = array_key_exists("time", $composer) ? date("d/m/Y", strtotime($composer["time"])) : "";
        $this->session->set(["MYREPORT_VERSION_DATE"], $versionDateRelease);
    }
}
