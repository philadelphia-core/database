<?php 

  namespace PhiladelPhia\Database\Interfaces;

	use PhiladelPhia\App\Interfaces\SettingsInterface;

  interface ManagerInterface {
    /**
     * Set settings to PDO for connected to Database.
     */
    public function __construct(SettingsInterface $settings);
  }