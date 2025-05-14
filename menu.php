<?php
require_once 'util.php';

class Menu {
    private $util;
    private $currentMenu;
    private $language;
    private $phone;

    public function __construct($phone, $sessionData = []) {
        $this->phone = $phone;
        $this->currentMenu = $sessionData['current_menu'] ?? 'main';
        $this->language = $sessionData['language'] ?? 'EN';
        
        // Create new Util instance without storing PDO in session
        $this->util = new Util($phone);
        $this->util->setLanguage($this->language);
    }

    public function getSessionData() {
        return [
            'current_menu' => $this->currentMenu,
            'language' => $this->language,
            'phone' => $this->phone
        ];
    }

    public function handleInput($input) {
        switch ($this->currentMenu) {
            case 'main':
                return $this->handleMainMenu($input);
            case 'assess_feelings':
                return $this->handleFeelingsAssessment($input);
            case 'talk_counselor':
                return $this->handleCounselorSelection($input);
            case 'hospital_contact':
                return $this->handleHospitalSelection($input);
            case 'language':
                return $this->handleLanguageSelection($input);
            default:
                return $this->showMainMenu();
        }
    }

    private function handleMainMenu($input) {
        switch ($input) {
            case '1':
                $this->currentMenu = 'assess_feelings';
                return $this->util->getTranslation('feelings_question') . "\n0. Back\n00. Back to main menu";
            case '2':
                $this->currentMenu = 'talk_counselor';
                $counselors = $this->util->getCounselors();
                $message = $this->util->getTranslation('counselor_list');
                foreach ($counselors as $index => $counselor) {
                    $message .= ($index + 1) . ". " . $counselor['name'] . " (" . $counselor['specialty'] . ")\n";
                }
                $message .= "0. Back\n00. Back to main menu";
                return $message;
            case '3':
                $this->currentMenu = 'hospital_contact';
                $hospitals = $this->util->getHospitals();
                $message = $this->util->getTranslation('hospital_list');
                foreach ($hospitals as $index => $hospital) {
                    $message .= ($index + 1) . ". " . $hospital['name'] . " (" . $hospital['location'] . ")\n";
                }
                $message .= "0. Back\n00. Back to main menu";
                return $message;
            case '4':
                $this->currentMenu = 'language';
                return $this->util->getTranslation('language_options') . "\n0. Back\n00. Back to main menu";
            case '0':
            case '00':
            case '98':
            case '99':
                // Back or main menu - remains on main menu
                return $this->showMainMenu();
            default:
                return $this->util->getTranslation('invalid_option') . "\n" . $this->showMainMenu();
        }
    }

    private function handleFeelingsAssessment($input) {
        $responses = [
            '1' => 'bad_feeling',
            '2' => 'very_bad_feeling',
            '3' => 'good_feeling',
            '4' => 'very_good_feeling'
        ];

        if (isset($responses[$input])) {
            $this->util->sendCounselorNotification($input, $this->phone);
            $this->currentMenu = 'main';
            return $this->util->getTranslation($responses[$input]) . "\n" . $this->showMainMenu();
        } elseif ($input == '0' || $input == '00' || $input == '98') {
            $this->currentMenu = 'main';
            return $this->showMainMenu();
        } else {
            return $this->util->getTranslation('invalid_option') . "\n" . $this->util->getTranslation('feelings_question') . "\n0. Back\n00. Back to main menu";
        }
    }

    private function handleCounselorSelection($input) {
        $counselors = $this->util->getCounselors();
        
        if ($input == '0' || $input == '00' || $input == '98') {
            $this->currentMenu = 'main';
            return $this->showMainMenu();
        } elseif (is_numeric($input) && $input > 0 && $input <= count($counselors)) {
            $counselor = $counselors[$input - 1];
            $this->util->sendCounselorNotification('User requested contact', $this->phone);
            $this->currentMenu = 'main';
            return "You've selected " . $counselor['name'] . ". They will contact you shortly.\n" . $this->showMainMenu();
        } else {
            $message = $this->util->getTranslation('counselor_list');
            foreach ($counselors as $index => $counselor) {
                $message .= ($index + 1) . ". " . $counselor['name'] . " (" . $counselor['specialty'] . ")\n";
            }
            return $this->util->getTranslation('invalid_option') . "\n" . $message . "0. Back\n00. Back to main menu";
        }
    }

    private function handleHospitalSelection($input) {
        $hospitals = $this->util->getHospitals();
        
        if ($input == '0' || $input == '00' || $input == '98') {
            $this->currentMenu = 'main';
            return $this->showMainMenu();
        } elseif (is_numeric($input) && $input > 0 && $input <= count($hospitals)) {
            $this->util->sendHospitalInfo($hospitals[$input - 1]['id']);
            $this->currentMenu = 'main';
            return $this->util->getTranslation('hospital_info') . "\n" . $this->showMainMenu();
        } else {
            $message = $this->util->getTranslation('hospital_list');
            foreach ($hospitals as $index => $hospital) {
                $message .= ($index + 1) . ". " . $hospital['name'] . " (" . $hospital['location'] . ")\n";
            }
            return $this->util->getTranslation('invalid_option') . "\n" . $message . "0. Back\n00. Back to main menu";
        }
    }

    private function handleLanguageSelection($input) {
        $languages = ['1' => 'EN', '2' => 'RW', '3' => 'FR', '4' => 'SW'];
        
        if (isset($languages[$input])) {
            $this->language = $languages[$input];
            $this->util->setLanguage($this->language);
            $this->currentMenu = 'main';
            return "Language set to " . $languages[$input] . "\n" . $this->showMainMenu();
        } elseif ($input == '0' || $input == '00' || $input == '98') {
            $this->currentMenu = 'main';
            return $this->showMainMenu();
        } else {
            return $this->util->getTranslation('invalid_option') . "\n" . $this->util->getTranslation('language_options') . "\n0. Back\n00. Back to main menu";
        }
    }

    private function showMainMenu() {
        return $this->util->getTranslation('welcome');
    }

    public function getCurrentMenu() {
        return $this->currentMenu;
    }
    
    public function getLanguage() {
        return $this->language;
    }
}