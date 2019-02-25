<?php

namespace Framework_life_balance\core_components;

use \Framework_life_balance\core_components\Accumulation;
use PHPMailer\PHPMailer\Exception;

/**
 * Суть решений
 *
 * @package Framework_life_balance\core_components
 *
 */
class Orientation
{
    /**
     * Включаем контроль ядра
     *
     * @return null
     */
    static function initiation(){

        /* Берём настройки системы из файла */
        $config_system = Accumulation::include_information_from_file(DIR_SOLUTIONS,'Настройка системы','php');

        if($config_system === null){
            Motion::fix_error('нет файла настройки системы',__FILE__, __LINE__);
        }

        /* Устанавливаем настройки системы */
        Representation::set_mission('config_system',$config_system);

        /* Определяем операционную систему */
        self::detect_operating_system();

        /* Устанавливаем языковой стандарт */
        setlocale(LC_ALL, $config_system['locale']);

        /* Отключаем вывод ошибок в интерфейс */
        error_reporting(0);

        /* Устанавливаем временнную зону */
        date_default_timezone_set($config_system['time_zone']);

        /* Включаем выявление ошибки */
        register_shutdown_function(function(){
            self::detect_error();
        });

    }

    /*---------------------------------------------------------*/
    /*-----------------------КОНТРОЛЬ--------------------------*/
    /*---------------------------------------------------------*/

    /**
     * Проверяем запрос на правомерность
     *
     * @return null
     */
    static function check_request_legality(){

        /*запрошенная наработка*/
        $request_experience = Representation::get_mission('request_experience');

        /*запрошенная наработанная цель*/
        $request_experience_goal = Representation::get_mission('request_experience_goal');

        /*Проверяем правильное взятие норматива наработок*/
        self::check_correct_taking_schema_experience($request_experience, $request_experience_goal, null, 'stop');

    }

    /**
     * Проверяем запрос на деструктив
     *
     * @return null
     */
    static function check_request_destructive(){

        /*получаем параметры запроса*/
        $parameters_request = Representation::get_mission('parameters_request');

        if(count($parameters_request)==0){
            return;
        }

        /*губительные данные*/
        $destructive_data =[
            '<script',
            '<frame',
        ];

        foreach($parameters_request as $key=>$value){
            foreach($destructive_data as $string){
                if(substr_count(mb_strtolower($value),mb_strtolower($string))){
                    Motion::fix_error('обнаружены губительные данные ('.htmlspecialchars($string).') в '.htmlspecialchars($key).': '.htmlspecialchars($value),__FILE__,__LINE__, 'stop');
                }
            }
        }

    }

    /**
     * Проверяем изменения в схеме базы данных
     *
     * @return null
     */
    static function check_changes_schema_data_base(){

        /*запрошенная наработка*/
        $request_experience = Representation::get_mission('request_experience');

        /*запрошенная наработанная цель*/
        $request_experience_goal = Representation::get_mission('request_experience_goal');

        /*для такой Цели делать проверку нет надобности*/
        if(($request_experience.'/'.$request_experience_goal) == 'control/reassembly_data_base'){
            return;
        }

        /* Реализованный норматив таблиц базы данных */
        $realized_schema_data_base = Accumulation::get_information_realized_schema_data_base();

        /* Текущий норматив таблиц базы данных */
        $schema_data_base = Representation::get_mission('schema_data_base');

        /*Сопоставляем норматива базы данных*/
        $changes = self::matching_schema_data_base($realized_schema_data_base, $schema_data_base);

        /*есть изменения*/
        if($changes){

            /* Проверяем запущен ли процес реструктуризации */
            if(Accumulation::include_information_from_file(DIR_PROTOCOLS_PROCESSES, 'Текущая реконструкция базы данных','log') === null){

                /* Фиксируем реконструкцию базы данных */
                Motion::fix_reassembly_data_base('Вызов');

                /*вызываем консоль наработку реструктуризации базы данных*/
                Motion::call_console_experience('control', 'reassembly_data_base', []);

            }

            if(Representation::get_mission('user_device') != 'console'){

                /*Ставим заглушку сообщающую о технических работах*/
                Motion::fix_error('технические работы с базой данных',__FILE__,__LINE__, 'engineering_works');

            }
        }

    }

    /**
     * Проверяем правомерность запроса
     *
     * @return boolean
     */
    static function check_request_access()
    {

        /* Запрощенная наработка */
        $request_experience = Representation::get_mission('request_experience');

        /* Запрощенная наработанная цель */
        $request_experience_goal = Representation::get_mission('request_experience_goal');

        /* Кому предназначена наработанная цель */
        $experience_goal_intended = Accumulation::schema_experience($request_experience, $request_experience_goal, 'intended');

        switch ($experience_goal_intended) {
            /*всем*/
            case 'any':
                return true;
                break;
            /*только для не авторизованных*/
            case 'unauthorized':
                if (!Motion::data_authorized_user()){
                    return true;
                }
                else{
                    Motion::fix_error('only_unauthorized',__FILE__,__LINE__,'stop');
                }
                break;
            /*только для авторизованных*/
            case 'authorized':
                if (Motion::data_authorized_user()){
                    return true;
                }
                else{
                    Motion::fix_error('only_authorized',__FILE__,__LINE__,'stop');
                }
                break;
            /*только для администраторов*/
            case 'authorized_by_administrator':
                if (Motion::data_authorized_user() and Motion::data_authorized_user('is_admin') == 'true'){
                    return true;
                }
                else{
                    Motion::fix_error('only_authorized_by_admin',__FILE__,__LINE__,'stop');
                }
                break;
            /*только для запуска из под консоли*/
            case 'console':
                if(Representation::get_mission('user_device') == 'console'){
                    return true;
                }
                else{
                    Motion::fix_error('only_console',__FILE__,__LINE__,'stop');
                }
                break;
        }

    }

    /**
     * Проверяем правильное взятие норматива наработок
     *
     * @param string $experience наработка
     * @param string $goal цель
     * @param string $detail деталь
     * @param string $call_index_goal_on_error вызвать наработанную index цель при ошибке
     */
    static function check_correct_taking_schema_experience($experience = null, $goal = null, $detail = null, $call_index_goal_on_error = 'error'){

        /*получаем схему наработок*/
        $schema_experiences = Representation::get_mission('schema_experiences');

        if($schema_experiences == null){
            Motion::fix_error('нет номратива наработок',__FILE__,__LINE__, $call_index_goal_on_error);
        }

        /*проверка*/
        if($experience!=null and !isset($schema_experiences[$experience])){
            Motion::fix_error('нет функции сайта '.$experience,__FILE__,__LINE__, $call_index_goal_on_error);
        }
        elseif($goal!=null and !isset($schema_experiences[$experience]['goals'][$goal])){
            Motion::fix_error('Цели '.$goal.' нет в функции сайта '.$experience,__FILE__,__LINE__, $call_index_goal_on_error);
        }
        elseif($experience!=null and $goal==null and $detail!=null and !isset($schema_experiences[$experience][$detail])){
            Motion::fix_error('нет детали '.$detail.' у функций сайта '.$experience,__FILE__,__LINE__, $call_index_goal_on_error);
        }
        elseif($goal!=null and $detail!=null and !isset($schema_experiences[$experience]['goals'][$goal][$detail])){
            Motion::fix_error('нет детали '.$detail.' у Цели '.$goal.' в наработке '.$experience,__FILE__,__LINE__, $call_index_goal_on_error);
        }

    }

    /**
     * Проверяем правильное взятие норматива базы данных
     *
     * @param string $table наработка
     * @param string $column цель
     * @param string $detail деталь
     * @param string $call_index_goal_on_error вызвать наработанную index цель при ошибке
     */
    static function check_correct_taking_schema_data_base($table = null, $column = null, $detail = null, $call_index_goal_on_error = 'error'){

        /*получаем схему базы данных*/
        $schema_data_base = Representation::get_mission('schema_data_base');

        if($schema_data_base == null){
            Motion::fix_error('нет норматива базы данных',__FILE__,__LINE__, $call_index_goal_on_error);
        }

        /*проверка*/
        if($table!=null and !isset($schema_data_base[$table])){
            Motion::fix_error('нет таблицы '.$table,__FILE__,__LINE__, $call_index_goal_on_error);
        }
        elseif($column!=null and !isset($schema_data_base[$table]['columns'][$column])){
            Motion::fix_error('колонки '.$column.' нет в таблице '.$table,__FILE__,__LINE__, $call_index_goal_on_error);
        }
        elseif($table!=null and $column==null and $detail!=null and !isset($schema_data_base[$table][$detail])){
            Motion::fix_error('нет детали '.$detail.' у таблицы '.$table,__FILE__,__LINE__, $call_index_goal_on_error);
        }
        elseif($column!=null and $detail!=null and !isset($schema_data_base[$table]['columns'][$column][$detail])){
            Motion::fix_error('нет детали '.$detail.' у колонки '.$column.' в таблице '.$table,__FILE__,__LINE__, $call_index_goal_on_error);
        }

    }

    /**
     * Позиция во времени
     *
     * @param string $format формат даты
     * @return string $date
     * @throws
     */
    static function position_time($format = 'Y-m-d H:i:s')
    {
        try{
            $date_class = new \DateTime();
            $date = $date_class->format($format);

            return $date;
        }
        catch (\Exception $e){
            Motion::fix_error($e->getMessage(),__FILE__,__LINE__);
        }
    }

    /**
     * Помечаем начало выполнения функции сайта
     *
     * @return null
     */
    static function mark_start_execution_experience(){

        /*устанавливаем время вызова функци сайта*/
        Representation::set_mission('mark_time_call_experience',time());

        /*вызванная наработка*/
        $call_experience = Representation::get_mission('call_experience');

        /*вызванная наработанная цель*/
        $call_experience_goal = Representation::get_mission('call_experience_goal');

        /*выделенное время на выполнение наработанной Цели*/
        $lead_time_seconds = Accumulation::schema_experience($call_experience, $call_experience_goal, 'lead_time');

        if(!$lead_time_seconds){
            $lead_time_seconds = 1;
        }

            set_time_limit(($lead_time_seconds + 5));

    }

    /**
     * Помечаем завершение выполнения функции сайта
     *
     * @return null
     */
    static function mark_stop_execution_experience(){

        /*вычисляем время выполнения*/
        $lead_time_executed = time() - Representation::get_mission('mark_time_call_experience');

        /*время выполнения*/
        Representation::set_mission('lead_time_executed',$lead_time_executed);

        /*вызванная наработка*/
        $call_experience = Representation::get_mission('call_experience');

        /*вызванная наработанная цель*/
        $call_experience_goal = Representation::get_mission('call_experience_goal');

        /*выделенное время на выполнение наработанной Цели*/
        $lead_time_seconds = Accumulation::schema_experience($call_experience, $call_experience_goal, 'lead_time');

        /*обнаружено превышение времени выполнения*/
        if($lead_time_executed>$lead_time_seconds){
            Motion::fix_error('Превышения выполнения цели '.$call_experience_goal.' функции сайта '.$call_experience.' на ' . ($lead_time_executed-$lead_time_seconds) . ' сек.',__FILE__,__LINE__,false);
        }

    }

    /**
     * Выявляем ошибку
     *
     * @return null
     */
    static function detect_error()
    {

        if (Representation::get_mission('message_crash')==null and @is_array($e = @error_get_last())) {

            /*данные на ошибку*/
            $error_no = isset($e['type']) ? $e['type'] : 0;
            $error_message = isset($e['message']) ? $e['message'] : '';
            $file_name = isset($e['file']) ? $e['file'] : '';
            $file_line = isset($e['line']) ? $e['line'] : '';

            if ($error_no > 0) {
                Motion::fix_error($error_message, $file_name, $file_line);
            }

        }

    }

    /**
     * Определяем операционную систему
     *
     * @return null
     */
    static function detect_operating_system(){
        /*windows*/
        if (substr(php_uname(), 0, 7) == "Windows"){
            Representation::set_mission('operating_system','windows');
        }
        /*unix*/
        else{
            Representation::set_mission('operating_system','unix');
        }
    }

    /**
     * Проверяем правомерность ответа
     *
     * @return null
     */
    static function check_answer_correct(){

        /*Выявляем ошибку*/
        Orientation::detect_error();

        /*вызванная наработка*/
        $call_experience = Representation::get_mission('call_experience');

        /*вызванная наработанная цель*/
        $call_experience_goal = Representation::get_mission('call_experience_goal');

        /*формат результата наработанной Цели*/
        $format_result = Accumulation::schema_experience($call_experience, $call_experience_goal, 'format_result');

        /*результат выполнения наработанной Цели*/
        $result_executed = Representation::get_mission('result_executed');

        if($format_result == 'array' and !is_array($result_executed)){
            Motion::fix_error('no_array_in_result_executed',__FILE__,__LINE__);
        }
        elseif($format_result == 'text' and is_array($result_executed)){
            Motion::fix_error('no_content_in_result_executed',__FILE__,__LINE__);
        }

    }

    /**
     * Прекращаем работу ядра
     *
     * @return null
     */
    static function stop_core(){

        /*Завершаем коммуникацию с базой данных*/
        Accumulation::complete_communication_with_data_base();

        /*Завершаем коммуникацию с памятью*/
        Accumulation::complete_communication_with_memory();

        /*Завершаем коммуникацию с почтой*/
        Accumulation::complete_communication_with_mail();

        /*Удаляем все предназначения*/
        Representation::delete_all_missions();

        exit;
    }

    /*---------------------------------------------------------*/
    /*-------------------СТРУКТУРИРОВАНИЕ----------------------*/
    /*---------------------------------------------------------*/

    /**
     * Разбираем запрос
     *
     * @return null
     */
    static function parse_request(){

        if(isset($_SERVER['argv'][0])){

            /*устройство пользователя*/
            $user_device = 'console';

            /*наработка*/
            $request_experience  = (isset($_SERVER['argv'][1]) and $_SERVER['argv'][1]!='') ? $_SERVER['argv'][1] : 'index';

            /*цель Наработки*/
            $request_experience_goal = (isset($_SERVER['argv'][2]) and $_SERVER['argv'][2]!='') ? $_SERVER['argv'][2] : 'index';

        }
        else{

            /*устройство пользователя*/
            $user_device = 'browser';

            /*разбираем запрос Наработки*/
            $request_experience_explode = !empty($_GET['request']) ? explode('/', $_GET['request']) : [];

            /*наработка*/
            $request_experience  = (isset($request_experience_explode[1]) and $request_experience_explode[1]!='') ? $request_experience_explode[1] : 'index';

            /*цель Наработки*/
            $request_experience_goal = (isset($request_experience_explode[2]) and $request_experience_explode[2]!='') ? $request_experience_explode[2] : 'index';

        }

        /*формируем наработку*/
        $request_experience = htmlspecialchars(mb_strtolower($request_experience));

        /*формируем цель Наработки*/
        $request_experience_goal = htmlspecialchars(mb_strtolower($request_experience_goal));

        /*устанавливаем откуда запрос*/
        Representation::set_mission('user_device',$user_device);

        /*устанавливаем запрошенные Наработки*/
        Representation::set_mission('request_experience',$request_experience);

        /*устанавливаем запрошенную наработанную цель*/
        Representation::set_mission('request_experience_goal',$request_experience_goal);

    }

    /**
     * Разбираем параметры запроса
     *
     * @return null
     */
    static function parse_parameters_request(){

        /*параметры запроса*/
        $parameters_request = [];

        /*получаем откуда запрос*/
        $user_device = Representation::get_mission('user_device');

        if($user_device == 'console'){

            /*паметры запроса*/
            if(isset($_SERVER['argv'][3]) and $_SERVER['argv'][3]>0){

                /* Получаем параметры из базы данных */
                $request_console = Accumulation::interchange_information_with_data_base('Получение', 'Информации о запуске из консоли по id', [
                    ':id' => $_SERVER['argv'][3]
                ]);

                if($request_console and isset($request_console['parameters'])){

                    /* Обновляем статус запроса консоли в базе данных */
                    Accumulation::interchange_information_with_data_base('Изменение', 'Статуса запуска консоли', [
                        ':id'     => $_SERVER['argv'][3],
                        ':status' => 'do',
                    ]);

                    /*параметры запроса*/
                    $parameters_request = json_decode($request_console['parameters'],1);

                }
                else{
                    $parameters_request = [];
                }

            }
            else{
                $parameters_request = [];
            }

        }
        elseif($user_device == 'browser'){
            /*параметры запроса*/
            $parameters_request = (array)@$_GET + (array)@$_POST;
        }
        else{
            Motion::fix_error('unknown user_device: ' . $user_device,__FILE__, __LINE__);
        }

        /*устанавливаем параметры запроса*/
        Representation::set_mission('parameters_request',$parameters_request);

    }

    /**
     * Разбираем авторизованность
     *
     * @return null
     */
    static function parse_authorized(){

        if(isset($_COOKIE["user_id"]) and $_COOKIE["user_id"]!=false and isset($_COOKIE["user_session"]) and $_COOKIE["user_session"] == Orientation::formation_user_session($_COOKIE["user_id"])){

            /*индификационный номер пользователя*/
            $user_id = $_COOKIE["user_id"];

            /*сессия пользователя*/
            $user_session = $_COOKIE["user_session"];

            /*устанавливаем индификационный номер пользователя*/
            Representation::set_mission('user_id',$user_id);

            /*устанавливаем сессию пользователя*/
            Representation::set_mission('user_session',$user_session);

        }

        /*устанавливаем удалённый адрес пользователя*/
        Representation::set_mission('user_ip',Orientation::definition_user_ip());

    }

    /**
     * Формируем сессию пользователя
     *
     * @param string $user_id индификационный номер пользователя
     * @return string $session сессия пользователя
     */
    static function formation_user_session($user_id){

        $session = md5('formation-'.$user_id.'-'.$_SERVER['SERVER_ADDR']);

        return $session;
    }

    /**
     * Формируем пароль пользователя
     *
     * @param string $password пароль пользователя
     * @return string $password_formation сформированный пароль пользователя
     */
    static function formation_user_password($password){

        $password_formation = md5('formation-'.$password);

        return $password_formation;
    }

    /**
     * Формируем результат выполенения в интерфейс
     *
     * @return string $answer текст ответа
     */
    static function formation_result_executed_to_interface(){

        /*текст ответа*/
        $answer ='';

        /*вызванная наработка*/
        $call_experience = Representation::get_mission('call_experience');

        /*вызванная наработанная цель*/
        $call_experience_goal = Representation::get_mission('call_experience_goal');

        /* Схема вызванной наработанной цели */
        $schema_call_experience_goal = Accumulation::schema_experience($call_experience, $call_experience_goal);

        /*результат выполнения наработанной Цели*/
        $content = Representation::get_mission('result_executed');

        /* Формируем содержимое ответа */
        switch ($schema_call_experience_goal['format_result']){
            case 'text':
                $answer = $content;
                break;
            case 'array':

                /* Категория */
                $result_executed['category'] = Representation::get_mission('call_experience');

                /* Цель */
                $result_executed['goal'] = Representation::get_mission('call_experience_goal');

                /*заголовок*/
                $result_executed['title'] = htmlspecialchars($schema_call_experience_goal['Заголовок страницы']);

                /*короткое описание*/
                $result_executed['description'] = htmlspecialchars($schema_call_experience_goal['Описание страницы']);

                /*ключевое описание*/
                $result_executed['keywords'] = htmlspecialchars($schema_call_experience_goal['Ключевики страницы']);

                /* Содержание */
                $result_executed['content'] = $content;

                /*кодируем ответ по json*/
                $answer = json_encode($result_executed);

                break;
        }

        /* Ответ в браузер */
        if(Representation::get_mission('user_device')=='browser'){

            /*всегда 200 код ответа*/
            http_response_code(200);

            /*объявляем запрет на кэширование*/
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Expires: " . Orientation::position_time("r"));

            if($schema_call_experience_goal['format_result'] == 'array'){

                header('Content-Type: application/json');

            }

        }
        /* Ответ в консоль */
        elseif(Representation::get_mission('user_device')=='console'){

        }

        return $answer;

    }

    /**
     * Определение удалённого адреса пользователя
     *
     * @return string $user_ip
     */
    static function definition_user_ip(){

        if(isset($_SERVER['REMOTE_ADDR'])){
            $user_ip = $_SERVER['REMOTE_ADDR'];
        }
        else{
            $user_ip = '127.0.0.1';
        }

        return $user_ip;

    }

    /**
     * Формирование шаблона
     *
     * @param string $template шаблон
     * @param array $parameters параметры
     * @return string
     */
    static function formation_template($template,$parameters){

        /*получаем шаблон*/
        $body = Accumulation::include_information_from_file(DIR_HTML,$template,'html');

        if($body === null){
            Motion::fix_error('нет файла html шаблона: '.$template,__FILE__,__LINE__);
        }

        foreach($parameters as $key=>$value){
            $body = str_replace('{'.$key.'}',$value,$body);
        }

        return $body;

    }

    /**
     * Формирование ссылки проекта
     *
     * @return string
     */
    static function formation_url_project(){

        /*получаем настройки системы*/
        $config_system = Representation::get_mission('config_system');

        /*получаем настройки проекта*/
        $config_project = Representation::get_mission('config_project');

        /*ссылка проекта*/
        $url_project = (($config_system['inclusiveness_ssl'])?'https':'http').'://'.$config_project['url'];

        return $url_project;

    }

    /**
     * Формируем консольную консольную команду вызова Наработки
     *
     * @param string $experience наработка
     * @param string $experience_goal цель
     * @param integer $id_save_parameters id сохранённых параметров
     * @return string
     */
    static function formation_console_command_call_experience($experience, $experience_goal, $id_save_parameters){

        $command = self::detect_path_executable_php() . ' ' . DIR_ROOT . 'Ядро.php' . ' ' . $experience . ' ' . $experience_goal . ' ' . $id_save_parameters;

        /*команда для windows*/
        if(Representation::get_mission('operating_system') == "windows"){
            $command = "start /B " . $command;
        }
        /*команда для unix*/
        elseif(Representation::get_mission('operating_system') == "unix"){
            $command = $command . " > /dev/null &";
        }
        else{
            Motion::fix_error('no operating_system',__FILE__,__LINE__);
        }

        return $command;

    }

    /**
     * Определяем путь до исполнителя PHP
     *
     * @return string
     */
    static function detect_path_executable_php(){

        $path_executable_php = false;

        $paths = explode(PATH_SEPARATOR, getenv('PATH'));

        foreach ($paths as $path){

            /*для windows xampp*/
            if(Representation::get_mission('operating_system') == "windows" and strstr($path, 'php.exe') and file_exists($path) and is_file($path)){
                $path_executable_php = $path;
                break;
            }
            else{

                /*предполагаем*/
                $path_executable_php = $path . DIRECTORY_SEPARATOR . "php" . (Representation::get_mission('operating_system') == "windows" ? ".exe" : "");

                if (file_exists($path_executable_php) && is_file($path_executable_php)) {
                    break;
                }
                else{
                    $path_executable_php = false;
                }

            }

        }

        if(!$path_executable_php){
            Motion::fix_error('no path_executable_php',__FILE__,__LINE__);
        }

        return $path_executable_php;
    }

    /**
     * Сопоставляем нормативы базы данных
     *
     * @param array $realized_schema реализованная схема
     * @param array $current_schema текущая схема
     * @return array|false
     */
    static function matching_schema_data_base($realized_schema, $current_schema){

        if(json_encode($realized_schema) == json_encode($current_schema)){
            return false;
        }

        $matching = [];

        foreach(['delete','create'] as $do){

            $schema_1 = [];
            $schema_2 = [];

            switch($do){
                case 'delete':
                    $schema_1 = $realized_schema;
                    $schema_2 = $current_schema;
                    break;
                case 'create':
                    $schema_1 = $current_schema;
                    $schema_2 = $realized_schema;
                    break;
            }

            /*проверяем*/
            foreach($schema_1 as $table=>$table_data){

                /*проявляем таблицу*/
                if(!isset($schema_2[$table])){
                    $matching[$do.'_table'][$table] = [
                        'description'             => $table_data['description'],
                        'primary_column'      => $table_data['primary_column'],
                        'primary_column_data' => $table_data['columns'][$table_data['primary_column']],
                    ];
                }
                elseif($do=='create' and $table_data['description']!=$schema_2[$table]['description']){

                    $matching['correct_comment_table'][$table] = [
                        'description' => $table_data['description']
                    ];

                }
                elseif($do=='create' and $table_data['primary_column']!=$schema_2[$table]['primary_column']){

                    $matching['correct_primary_column_table'][$table] = [
                        'primary_column'      => $table_data['primary_column'],
                        'primary_column_data' => $table_data['columns'][$table_data['primary_column']],
                    ];
                }

                /*проявляем колонки*/
                foreach($table_data['columns'] as $column=>$column_data){

                    if(!isset($schema_2[$table]['columns'][$column])){
                        if($table_data['primary_column']==$column){
                            continue;
                        }
                        $matching[$do.'_column'][$table][$column] = $column_data;
                    }
                    elseif($do=='create' and (
                        $schema_2[$table]['columns'][$column]['type']!=$column_data['type'] or
                        $schema_2[$table]['columns'][$column]['default']!=$column_data['default'] or
                        $schema_2[$table]['columns'][$column]['description']!=$column_data['description']
                        )){

                        $matching['correct_column'][$table][$column] = $column_data;

                    }

                }

                /*проявляем сортировку*/
                if(isset($table_data['sortings']) and count($table_data['sortings'])>0){

                    foreach($table_data['sortings'] as $sorting=>$sorting_data){

                        if(
                            !isset($schema_2[$table]['sortings'][$sorting])
                            or json_encode($schema_2[$table]['sortings'][$sorting])!=json_encode($sorting_data)
                        ){
                            $matching[$do.'_sortings'][$table][$sorting] = $sorting_data;
                        }
                    }

                }

                if(isset($table_data['references']) and count($table_data['references'])>0){

                    foreach($table_data['references'] as $reference=>$reference_data){

                        /*проявляем связи*/
                        if(
                            !isset($schema_2[$table]['references'][$reference])
                            or json_encode($schema_2[$table]['references'][$reference])!=json_encode($reference_data)
                        ){
                            $matching[$do.'_reference'][$table][$reference] = $reference_data;
                        }
                    }

                }

            }

        }

        return count($matching)>0 ? $matching : false;
    }

}