<?php
require_once 'dbconnection.php';
require_once 'sms.php';

class Util {
    private $db;
    private $phone;
    private $language = 'EN';
    
    // Constants for application
    const LANG_ENGLISH = 'EN';
    const LANG_KINYARWANDA = 'RW';
    const LANG_FRENCH = 'FR';
    const LANG_SWAHILI = 'SW';
    
    const FEELING_BAD = 1;
    const FEELING_VERY_BAD = 2;
    const FEELING_GOOD = 3;
    const FEELING_VERY_GOOD = 4;

    public function __construct($phone) {
        $this->phone = $this->sanitizePhoneNumber($phone);
        $this->initializeDatabaseConnection();
    }

    /**
     * Sanitize phone number by removing all non-numeric characters
     */
    private function sanitizePhoneNumber($phone) {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Initialize database connection with error handling
     */
    private function initializeDatabaseConnection() {
        try {
            $dbConnection = new DBConnection();
            $this->db = $dbConnection->getConnection();
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Service unavailable. Please try again later.");
        }
    }

    /**
     * Set application language
     */
    public function setLanguage($language) {
        $validLanguages = [
            self::LANG_ENGLISH,
            self::LANG_KINYARWANDA, 
            self::LANG_FRENCH,
            self::LANG_SWAHILI
        ];
        
        if (in_array($language, $validLanguages)) {
            $this->language = $language;
            return true;
        }
        return false;
    }

    public function getLanguage() {
        return $this->language;
    }

    public function getPhone() {
        return $this->phone;
    }

    /**
     * Get translation for current language
     */
    public function getTranslation($key) {
        $translations = $this->getAllTranslations();
        return $translations[$this->language][$key] ?? $translations[self::LANG_ENGLISH][$key];
    }

    /**
     * Define all application translations
     */
    private function getAllTranslations() {
        return [
            self::LANG_ENGLISH => $this->getEnglishTranslations(),
            self::LANG_KINYARWANDA => $this->getKinyarwandaTranslations(),
            self::LANG_FRENCH => $this->getFrenchTranslations(),
            self::LANG_SWAHILI => $this->getSwahiliTranslations()
        ];
    }

    private function getEnglishTranslations() {
        return [
            'welcome' => "Welcome to Ishingiro AI\n1. Assess My Feelings\n2. Talk to a Counselor\n3. Get Hospital Contact\n4. Language",
            'feelings_question' => "How do you feel today?\n1. Bad\n2. Very Bad\n3. Good\n4. Very Good",
            'counselor_list' => "Available Counselors:\n",
            'hospital_list' => "Nearby Hospitals:\n",
            'language_options' => "Select Language:\n1. English\n2. Kinyarwanda\n3. French\n4. Kiswahili",
            'thank_you' => "Thank you for using Ishingiro AI. A counselor will contact you shortly.",
            'hospital_info' => "Hospital information sent to your phone.",
            'invalid_option' => "Invalid option. Please try again.",
            'bad_feeling' => "We're sorry you're feeling bad. A counselor will reach out to help.",
            'very_bad_feeling' => "We're concerned about your response. A counselor will contact you immediately.",
            'good_feeling' => "We're glad you're feeling good! Let us know if you need anything.",
            'very_good_feeling' => "Great to hear you're feeling very good! Keep it up!"
        ];
    }

    private function getKinyarwandaTranslations() {
        return [
            'welcome' => "Murakaza neza kuri Ishingiro AI\n1. Gereranya imyumvire yanje\n2. Vugana n'umwarimu\n3. Kubona ibitaro\n4. Ururimi",
            'feelings_question' => "Urumva ute uyu munsi?\n1. Ntago ndumva neza\n2. Ndamutse nabi cyane\n3. Ndumva neza\n4. Ndumva neza cyane",
            'counselor_list' => "Abafasha bahari:\n",
            'hospital_list' => "Ibitaro bifitanye isano:\n",
            'language_options' => "Hitamo ururimi:\n1. Icyongereza\n2. Ikinyarwanda\n3. Igifaransa\n4. Igiswahili",
            'thank_you' => "Murakoze gukoresha Ishingiro AI. Umwarimu azakubwira vuba.",
            'hospital_info' => "Amakuru y'ibitaro yoherejwe kuri telefoni yawe.",
            'invalid_option' => "Hitamo ntibikunze. Gerageza nanone.",
            'bad_feeling' => "Turababwira ko tubabarira kutumva neza. Umwarimu azabakurikira.",
            'very_bad_feeling' => "Turabahangayikishije kubyo mwatanze. Umwarimu azabona vuba.",
            'good_feeling' => "Turakwishimira ko murumva neza! Nimba mufite icyo mukeneye, mubibwire.",
            'very_good_feeling' => "Neza cyane ko murumva neza cyane! Komeza gutyo!"
        ];
    }

    private function getFrenchTranslations() {
        return [
            'welcome' => "Bienvenue sur Ishingiro AI\n1. Évaluer mes sentiments\n2. Parler à un conseiller\n3. Obtenir les contacts d'hôpital\n4. Langue",
            'feelings_question' => "Comment vous sentez-vous aujourd'hui?\n1. Mal\n2. Très mal\n3. Bien\n4. Très bien",
            'counselor_list' => "Conseillers disponibles:\n",
            'hospital_list' => "Hôpitaux à proximité:\n",
            'language_options' => "Choisir la langue:\n1. Anglais\n2. Kinyarwanda\n3. Français\n4. Swahili",
            'thank_you' => "Merci d'utiliser Ishingiro AI. Un conseiller vous contactera bientôt.",
            'hospital_info' => "Informations sur l'hôpital envoyées à votre téléphone.",
            'invalid_option' => "Option invalide. Veuillez réessayer.",
            'bad_feeling' => "Nous sommes désolés que vous ne vous sentiez pas bien. Un conseiller vous contactera.",
            'very_bad_feeling' => "Nous sommes préoccupés par votre réponse. Un conseiller vous contactera immédiatement.",
            'good_feeling' => "Nous sommes heureux que vous vous sentiez bien! Faites-nous savoir si vous avez besoin de quelque chose.",
            'very_good_feeling' => "Super de savoir que vous vous sentez très bien! Continuez comme ça!"
        ];
    }

    private function getSwahiliTranslations() {
        return [
            'welcome' => "Karibu kwenye Ishingiro AI\n1. Tathmini hisia zangu\n2. Zungumza na mshauri\n3. Pata mawasiliano ya hospitali\n4. Lugha",
            'feelings_question' => "Unajisikiaje leo?\n1. Mbaya\n2. Mbaya sana\n3. Nzuri\n4. Nzuri sana",
            'counselor_list' => "Wasauri waliopo:\n",
            'hospital_list' => "Hospitali zilizo karibu:\n",
            'language_options' => "Chagua lugha:\n1. Kiingereza\n2. Kinyarwanda\n3. Kifaransa\n4. Kiswahili",
            'thank_you' => "Asante kwa kutumia Ishingiro AI. Mshauri atawasiliana nawe hivi karibuni.",
            'hospital_info' => "Taarifa za hospitali zimetumwa kwenye simu yako.",
            'invalid_option' => "Chaguo batili. Tafadhali jaribu tena.",
            'bad_feeling' => "Samahani kwa kuhisi vibaya. Mshauri atawasiliana nawe.",
            'very_bad_feeling' => "Tuna wasiwasi kwa jibu lako. Mshauri atawasiliana nawe mara moja.",
            'good_feeling' => "Tunafurahi kwamba unajisikia vizuri! Tuambie kama unahitaji chochote.",
            'very_good_feeling' => "Vizuri kusikia kwamba unajisikia vizuri sana! Endelea hivyo!"
        ];
    }

    /**
     * Get list of available counselors
     */
    public function getCounselors() {
        try {
            $stmt = $this->db->prepare(
                "SELECT id, name, specialty, phone 
                 FROM counselors 
                 WHERE is_active = 1 
                 ORDER BY name"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getCounselors: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get list of nearby hospitals
     */
    public function getHospitals() {
        try {
            $stmt = $this->db->prepare(
                "SELECT id, name, phone, location 
                 FROM hospitals 
                 ORDER BY name"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getHospitals: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Notify counselors about user's feelings
     */
    public function sendCounselorNotification($feelingLevel, $userId) {
        $feelingText = $this->getFeelingText($feelingLevel);
        $message = sprintf(
            "New user assessment:\nPhone: %s\nFeeling: %s\nLevel: %d",
            $this->phone,
            $feelingText,
            $feelingLevel
        );

        try {
            $sms = new sms($this->phone);
            $counselors = $this->getCounselors();
            
            foreach ($counselors as $counselor) {
                $sms->sendSMS($message, $counselor['phone'], 'ISHINGIRO');
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error in sendCounselorNotification: " . $e->getMessage());
            return false;
        }
    }

    private function getFeelingText($feelingLevel) {
        $feelings = [
            self::FEELING_BAD => 'Bad',
            self::FEELING_VERY_BAD => 'Very Bad',
            self::FEELING_GOOD => 'Good',
            self::FEELING_VERY_GOOD => 'Very Good'
        ];
        return $feelings[$feelingLevel] ?? 'Unknown';
    }

    /**
     * Send hospital information to user via SMS
     */
    public function sendHospitalInfo($hospitalId) {
        try {
            $hospital = $this->getHospitalDetails($hospitalId);
            
            if ($hospital) {
                $message = sprintf(
                    "Hospital Info:\nName: %s\nLocation: %s\nPhone: %s",
                    $hospital['name'],
                    $hospital['location'],
                    $hospital['phone']
                );
                
                $sms = new sms($this->phone);
                $result = $sms->sendSMS($message, $this->phone, 'ISHINGIRO');
                return $result !== false;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error in sendHospitalInfo: " . $e->getMessage());
            return false;
        }
    }

    private function getHospitalDetails($hospitalId) {
        try {
            $stmt = $this->db->prepare(
                "SELECT name, phone, location 
                 FROM hospitals 
                 WHERE id = ?"
            );
            $stmt->execute([$hospitalId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in getHospitalDetails: " . $e->getMessage());
            return false;
        }
    }
}