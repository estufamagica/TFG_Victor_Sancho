<?php

namespace App\Model\API;

class API_Wrapper
{
    protected String $username;
    protected String $password;
    private $remote_url;
    private $context;
    /*private $login_url='https://beedata.teamwork.com/authenticate.json';*/

    /**
     * @return String
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param String $username
     */
    public function setUsername(string $user): void
    {
        $this->username = $user;
        //self::$username = $user;
    }

    /**
     * @return String
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param String $password
     */
    public function setPassword(string $pass): void
    {
        $this->password = $pass;
        //self::$password = $pass;
    }




    /**
     * @param String $username
     * @param String $password
     */
    public function __construct(string $username, string $password)
    {
        @$this->username = $username;
        @$this->password = $password;
        if(!empty($username) && !empty($password)){
            $this->createContext();
        }

    }

    private function createContext(){
        // Create a stream
        $opts = array(
            'http'=>array(
                'method'=>"GET",
                'header' => "Authorization: Basic " . base64_encode("$this->username:$this->password")
            )
        );

        $this->context = stream_context_create($opts);
    }

    public function getAllProjects(){
        $this->remote_url = 'https://beedata.teamwork.com/projects.json';
        $this->createContext();
        return $this->returnFile();
    }

    public function getAllTaskLists(){
        $this->remote_url = 'https://beedata.teamwork.com/tasklists.json';
        $this->createContext();
        return $this->returnFile();
    }

    public function getTaskListsFromProject(string $id){
        $this->remote_url = 'https://beedata.teamwork.com/projects/' . $id . '/tasklists.json';

        return $this->returnFile();
    }

    public function getTasksFromTasklist(string $id){
        $this->remote_url = 'https://beedata.teamwork.com/tasklists/'. $id .'/tasks.json';

        return $this->returnFile();
    }

    public function getAllTimeEntriesForAllProjects(){
        $this->remote_url = 'https://beedata.teamwork.com/time_entries.json';

        return $this->returnFile();
    }

    public function getAllTimeEntriesForProject(string $id){

        $this->remote_url = 'https://beedata.teamwork.com/projects/'.$id.'/time_entries.json';

        return $this->returnFile();
    }

    public function getTotalTimeforProject(string $id){
        $this->remote_url = 'https://beedata.teamwork.com/projects/'.$id.'/time/total.json';

        return $this->returnFile();
    }

    public function getTotalTimeforAllProjects(){
        $this->remote_url = 'https://beedata.teamwork.com/projects/time/total.json';

        return $this->returnFile();
    }

    public function getAllTimeEntriesForTask(string $id){

        $this->remote_url = 'https://beedata.teamwork.com/tasks/'.$id.'/time_entries.json';

        return $this->returnFile();
    }

    public function getTotalTimeForTask(string $id){

        $this->remote_url = 'https://beedata.teamwork.com/tasks/'.$id.'/time/total.json';

        return $this->returnFile();
    }

    public function getTotalTimeforTasklist(string $id){
        $this->remote_url = 'https://beedata.teamwork.com/tasklists/'.$id.'/time/total.json';

        return $this->returnFile();
    }



    private function returnFile(){
        // Open the file using the HTTP headers set above
        return file_get_contents($this->remote_url, false, $this->context);
    }

    public function login(string $username, string $password){
        $this->setUsername($username);
        $this->setPassword($password);
        $this->createContext();
        $this->remote_url = 'https://beedata.teamwork.com/authenticate.json';
        return $this->returnFile();
    }

}