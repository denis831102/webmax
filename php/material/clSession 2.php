<?php  

class ClSession
{
    private int $deletedTime = 60 * 60 * 24 * 14; // 14 діб життя сесії
    private int $blockTime   = 60 * 5;            // 5 хв. блокування
    private int $countProba  = 5;                 // кількість спроб авторизації

    public function __construct()
    {
        ini_set('session.use_strict_mode', 1);
        $this->sessionCheck();
    }

    // запуск/проверка сессии
    private function sessionCheck(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $this->sessionRegenerateId();
        } elseif (
            !isset($_SESSION['deleted_time_session']) ||
            (time() - $_SESSION['deleted_time_session']) > $this->deletedTime
        ) {
            session_unset();
            session_destroy();
            ession_start();
            $this->sessionRegenerateId();
        }
    }

    // обновление ID
    private function sessionRegenerateId(): void
    {
        //$sessPref = session_create_id('s-');
        //session_id($sessPref);
        session_id('cli_test'); 
        //session_regenerate_id(true);

        $_SESSION['deleted_time_session'] = time();
        $_SESSION['check_proba'] ??= 0;
        $_SESSION['check_time']  ??= time();
    }

    public function getState(): array
    {
        return [
            'id'          => session_id(),
            'time'        => time() - ($_SESSION['deleted_time_session'] ?? time()),
            'isPermission'=> ($_SESSION['check_proba'] ?? 0) < $this->countProba,
            'timeProba'   => time() - ($_SESSION['check_time'] ?? time()),
            'countProba'  => $this->countProba - ($_SESSION['check_proba'] ?? 0),
        ];
    }

    public function getId(): string
    {
        return session_id();
    }

    public function getStatus(): int
    {
        return session_status();
    }

    public function setState(): array
    {
        $_SESSION['check_time']  ??= time();
        $_SESSION['check_proba'] ??= 0;

        if ((time() - $_SESSION['check_time']) > $this->blockTime) {
            $_SESSION['check_proba'] = 0;
            $_SESSION['check_time']  = time();
        } else {
            $_SESSION['check_proba']++;
        }

        return [
            'time'        => time() - $_SESSION['check_time'],
            'blockTime'   => $this->blockTime,
            'check_proba' => $_SESSION['check_proba'],
            'check_time'  => $_SESSION['check_time'],
        ];
    }
}

				
?> 