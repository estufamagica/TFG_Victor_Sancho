<?php

//require_once __DIR__ .'\../Model/API/API_Wrapper.php';

/*
// Create a stream
$opts = array(
    'http'=>array(
        'method'=>"GET",
        'header' => "Authorization: Basic " . base64_encode("$username:$password")
    )
);

$context = stream_context_create($opts);

// Open the file using the HTTP headers set above
//$file = file_get_contents($remote_url, false, $context);

*/
/*var_dump(@$API->getUsername());
var_dump(@$API->getPassword());*/

if (isset($_SESSION['email'])) {

    $API->setUsername($_SESSION['email']);
    $API->setPassword($_SESSION['passwordl']);

    $email = $_SESSION['email'];
    $password = $_SESSION['passwordl'];

    echo "<script> localStorage.setItem('email', '$email'); localStorage.setItem('pass', '$password');</script>";


    $file = $API->getAllProjects();
    $result = json_decode($file, true);
    $id = $result['projects'][0]['id'];

    $taskList = $API->getAllTaskLists();
    $tasklistjson = json_decode($taskList, true);

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){ //Case: form sent with search parameters, proceed to search the employees hours for the projects and tasks specified
        /*var_dump( $_POST['projects']);
        var_dump( $_POST['tasklist']);
        var_dump( $_POST['start_date']);*/
        $final_date = date("Y-m-d");
        if (!empty($_POST['final_date'])) {
            $final_date = $_POST['final_date'];
        }

        $projectsMap = array();

        if ($_POST['tasklist'] === '0'){ //No tasklist (client) has been selected
            if ($_POST['projects'] === '0'){ //No project has been selected -> all tasks from all tasklists from all projects

                $totalTimeProjects = json_decode($API->getTotalTimeforAllProjects(), true);   //Total All projects
                foreach ($totalTimeProjects['projects'] as $key => $value){

                    $tasklistforproject = json_decode($API->getTaskListsFromProject($value['id']), true);

                    foreach ($tasklistforproject['tasklists'] as $tasklists){
                        //$tasklisthoursforproject = json_decode($API->getTotalTimeforTasklist($tasklists['id']), true);
                        $tasksfortaskslist = json_decode($API->getTasksFromTasklist($tasklists['id']), true);

                        foreach ($tasksfortaskslist['todo-items'] as $tasks){
                            if($tasks['parentTaskId']===""){ //This means the task is a parent task -> ProjectBeeData
                                $tasktotaltime = json_decode($API->getTotalTimeForTask($tasks['id']), true);
                                $totalhoursprojectbee = (float)$tasktotaltime['projects'][0]['tasklist']['task']['time-totals']['total-hours-sum'];
                                if (!empty($tasks['predecessors'])){
                                    foreach ($tasks['predecessors'] as $childtasks){

                                        $childtasktotaltime = json_decode($API->getTotalTimeForTask($childtasks['id']), true);
                                        //$projectsMap[$value['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['total-hours'] += (float)$childtasktotaltime['projects'][0]['tasklist']['task']['time-totals']['total-hours-sum']; //stack tasks hours into projectsBee hours

                                        //$totalhoursprojectbee += (float)$childtasktotaltime['projects'][0]['tasklist']['task']['time-totals']['total-hours-sum']; //stack tasks hours into projectsBee hours
                                        if ($childtasktotaltime['projects'][0]['tasklist']['task']['time-totals']['total-hours-sum']!== '0'){
                                            $childtasktimeentries = json_decode($API->getAllTimeEntriesForTask($childtasks['id']), true);
                                            foreach ($childtasktimeentries['time-entries'] as $time_entries){
                                                $time_entry_date = substr($time_entries['date'],0, 10); //date format for time entry
                                                if ($time_entry_date >= $_POST['start_date'] && $time_entry_date <= $final_date) {
                                                    //Project Setting
                                                    $projectsMap[$value['id']]['id'] = $value['id'];
                                                    $projectsMap[$value['id']]['name'] = $value['name'];
                                                    @$projectsMap[$value['id']]['total-hours'] += (float)$time_entries['hoursDecimal'];
                                                    //Client setting
                                                    $projectsMap[$value['id']]['tasklists'][$tasklists['id']]['id'] = $tasklists['id'];
                                                    $projectsMap[$value['id']]['tasklists'][$tasklists['id']]['name'] = $tasklists['name'];

                                                    @$projectsMap[$value['id']]['tasklists'][$tasklists['id']]['total-hours'] += (float)$time_entries['hoursDecimal'];;
                                                    //Project Bee setting
                                                    $projectsMap[$value['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['id'] = $tasks['id'];
                                                    $projectsMap[$value['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['name'] = $tasks['content'];

                                                    @$projectsMap[$value['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['total-hours'] += (float)$time_entries['hoursDecimal'];
                                                    //Task setting
                                                    $projectsMap[$value['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['id'] = $childtasks['id'];
                                                    $projectsMap[$value['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['name'] = $childtasks['name'];
                                                    @$projectsMap[$value['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['total-hours'] += (float)$time_entries['hoursDecimal'];


                                                    //time entry setting
                                                    $projectsMap[$value['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['time-entries'][$time_entries['person-id']]['person-id'] = $time_entries['person-id'];
                                                    $projectsMap[$value['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['time-entries'][$time_entries['person-id']]['name'] = $time_entries['person-first-name'];
                                                    $projectsMap[$value['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['time-entries'][$time_entries['person-id']]['last-name'] = $time_entries['person-last-name'];
                                                    @$projectsMap[$value['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['time-entries'][$time_entries['person-id']]['total-hours'] += (float)$time_entries['hoursDecimal'];
                                                }

                                            }

                                        }
                                    }
                                }

                                if($totalhoursprojectbee!=0){ //This means the ProjectBee has time logged in but for a specific task
                                    $projectBeeTimeEntries = json_decode($API->getAllTimeEntriesForTask($tasks['id']), true);
                                    foreach ($projectBeeTimeEntries['time-entries'] as $pB_time_entries){
                                        $pb_time_entry_date = substr($pB_time_entries['date'],0,10);
                                        if ($pb_time_entry_date >= $_POST['start_date'] && $pb_time_entry_date <=$final_date){
                                            //Project Setting
                                            $projectsMap[$value['id']]['id'] = $value['id'];
                                            $projectsMap[$value['id']]['name'] = $value['name'];
                                            //Client setting
                                            $projectsMap[$value['id']]['tasklists'][$tasklists['id']]['id'] = $tasklists['id'];
                                            $projectsMap[$value['id']]['tasklists'][$tasklists['id']]['name'] = $tasklists['name'];
                                            //Project Bee setting
                                            $projectsMap[$value['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['id'] = $tasks['id'];
                                            $projectsMap[$value['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['name'] = $tasks['content'];

                                            $projectsMap[$value['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['time-entries'][$pB_time_entries['person-id']]['person-id'] = $pB_time_entries['person-id'];
                                            $projectsMap[$value['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['time-entries'][$pB_time_entries['person-id']]['name'] = $pB_time_entries['person-first-name'];
                                            $projectsMap[$value['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['time-entries'][$pB_time_entries['person-id']]['last-name'] = $pB_time_entries['person-last-name'];
                                            @$projectsMap[$value['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['time-entries'][$pB_time_entries['person-id']]['total-hours'] += (float)$pB_time_entries['hoursDecimal'];
                                            @$projectsMap[$value['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['total-hours'] += (float)$pB_time_entries['hoursDecimal'];
                                            @$projectsMap[$value['id']]['tasklists'][$tasklists['id']]['total-hours'] += (float)$pB_time_entries['hoursDecimal'];
                                            @$projectsMap[$value['id']]['total-hours'] += (float)$pB_time_entries['hoursDecimal'];
                                        }
                                    }
                                }
                            }

                        }
                    }

                }

            }
            else{ //Project selected -> all tasks for the tasklists of the project
                $totalTimeProjects = json_decode($API->getTotalTimeforProject($_POST['projects']), true);

                $tasklistforproject = json_decode($API->getTaskListsFromProject($totalTimeProjects['projects'][0]['id']), true);

                foreach ($tasklistforproject['tasklists'] as $tasklists){

                    $tasklisthoursforproject = json_decode($API->getTotalTimeforTasklist($tasklists['id']), true);

                    $tasksfortaskslist = json_decode($API->getTasksFromTasklist($tasklists['id']), true);

                    foreach ($tasksfortaskslist['todo-items'] as $tasks){
                        if($tasks['parentTaskId']===""){

                            $tasktotaltime = json_decode($API->getTotalTimeForTask($tasks['id']), true);
                            $totalhoursprojectbee = (float)$tasktotaltime['projects'][0]['tasklist']['task']['time-totals']['total-hours-sum'];
                            //$projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['total-hours'] = (float)$tasktotaltime['projects'][0]['tasklist']['task']['time-totals']['total-hours-sum'];
                            if (!empty($tasks['predecessors'])){
                                foreach ($tasks['predecessors'] as $childtasks){

                                    $childtasktotaltime = json_decode($API->getTotalTimeForTask($childtasks['id']), true);
                                    //$totalhoursprojectbee += (float)$childtasktotaltime['projects'][0]['tasklist']['task']['time-totals']['total-hours-sum'];

                                    if ($childtasktotaltime['projects'][0]['tasklist']['task']['time-totals']['total-hours-sum']!== '0'){
                                        $childtasktimeentries = json_decode($API->getAllTimeEntriesForTask($childtasks['id']), true);
                                        foreach ($childtasktimeentries['time-entries'] as $time_entries){
                                            $time_entry_date = substr($time_entries['date'],0, 10); //date format for time entry
                                            if ($time_entry_date >= $_POST['start_date'] && $time_entry_date <= $final_date) {
                                                //Project setting
                                                $projectsMap[$totalTimeProjects['projects'][0]['id']]['id'] = $totalTimeProjects['projects'][0]['id'];
                                                $projectsMap[$totalTimeProjects['projects'][0]['id']]['name'] = $totalTimeProjects['projects'][0]['name'];
                                                @$projectsMap[$totalTimeProjects['projects'][0]['id']]['total-hours'] += (float)$time_entries['hoursDecimal'];
                                                //Client setting
                                                $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['id'] = $tasklists['id'];
                                                $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['name'] = $tasklists['name'];
                                                @$projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['total-hours'] += (float)$time_entries['hoursDecimal'];
                                                //ProjectBee setting
                                                $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['id'] = $tasks['id'];
                                                $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['name'] = $tasks['content'];
                                                @$projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['total-hours'] += (float)$time_entries['hoursDecimal'];
                                                //Task setting
                                                $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['id'] = $childtasks['id'];
                                                $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['name'] = $childtasks['name'];
                                                @$projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['total-hours'] += (float)$time_entries['hoursDecimal'];

                                                //Time entry setting
                                                $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['time-entries'][$time_entries['person-id']]['person-id'] = $time_entries['person-id'];
                                                $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['time-entries'][$time_entries['person-id']]['name'] = $time_entries['person-first-name'];
                                                $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['time-entries'][$time_entries['person-id']]['last-name'] = $time_entries['person-last-name'];
                                                @$projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['time-entries'][$time_entries['person-id']]['total-hours'] += (float)$time_entries['hours'];
                                            }
                                        }
                                    }
                                }
                            }

                            if($totalhoursprojectbee!=0){ //This means the ProjectBee has time logged in but for a specific task
                                $projectBeeTimeEntries = json_decode($API->getAllTimeEntriesForTask($tasks['id']), true);
                                foreach ($projectBeeTimeEntries['time-entries'] as $pB_time_entries){
                                    $pb_time_entry_date = substr($pB_time_entries['date'],0,10);
                                    if ($pb_time_entry_date >= $_POST['start_date'] && $pb_time_entry_date <=$final_date){
                                        //Project setting
                                        $projectsMap[$totalTimeProjects['projects'][0]['id']]['id'] = $totalTimeProjects['projects'][0]['id'];
                                        $projectsMap[$totalTimeProjects['projects'][0]['id']]['name'] = $totalTimeProjects['projects'][0]['name'];
                                        //Client setting
                                        $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['id'] = $tasklists['id'];
                                        $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['name'] = $tasklists['name'];
                                        //ProjectBee setting
                                        $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['id'] = $tasks['id'];
                                        $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['name'] = $tasks['content'];


                                        $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['time-entries'][$pB_time_entries['person-id']]['person-id'] = $pB_time_entries['person-id'];
                                        $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['time-entries'][$pB_time_entries['person-id']]['name'] = $pB_time_entries['person-first-name'];
                                        $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['time-entries'][$pB_time_entries['person-id']]['last-name'] = $pB_time_entries['person-last-name'];
                                        @$projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['time-entries'][$pB_time_entries['person-id']]['total-hours'] += (float)$pB_time_entries['hoursDecimal'];
                                        @$projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['projectsBee'][$tasks['id']]['total-hours'] += (float)$pB_time_entries['hoursDecimal'];
                                        @$projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$tasklists['id']]['total-hours'] += (float)$pB_time_entries['hoursDecimal'];
                                        @$projectsMap[$totalTimeProjects['projects'][0]['id']]['total-hours'] += (float)$pB_time_entries['hoursDecimal'];
                                    }
                                }
                            }

                        }
                    }
                }
            }
        }
        else // Tasklist selected (client) -> all tasks for this tasklist
        {


            $totalTimeTasklists = json_decode($API->getTotalTimeforTasklist($_POST['tasklist']), true);
            $totalTimeProjects = json_decode($API->getTotalTimeforProject($totalTimeTasklists['projects'][0]['id']), true);





            $tasksfortaskslist = json_decode($API->getTasksFromTasklist($totalTimeTasklists['projects'][0]['tasklist']['id']), true);

            foreach ($tasksfortaskslist['todo-items'] as $tasks){
                if($tasks['parentTaskId']===""){

                    $tasktotaltime = json_decode($API->getTotalTimeForTask($tasks['id']), true);
                    $totalhoursprojectbee = (float)$tasktotaltime['projects'][0]['tasklist']['task']['time-totals']['total-hours-sum'];
                    if (!empty($tasks['predecessors'])){
                        foreach ($tasks['predecessors'] as $childtasks){

                            $childtasktotaltime = json_decode($API->getTotalTimeForTask($childtasks['id']), true);
                            //$totalhoursprojectbee += (float)$childtasktotaltime['projects'][0]['tasklist']['task']['time-totals']['total-hours-sum'];

                            if ($childtasktotaltime['projects'][0]['tasklist']['task']['time-totals']['total-hours-sum']!== '0'){
                                $childtasktimeentries = json_decode($API->getAllTimeEntriesForTask($childtasks['id']), true);
                                foreach ($childtasktimeentries['time-entries'] as $time_entries){
                                    $time_entry_date = substr($time_entries['date'],0, 10); //date format for time entry
                                    if ($time_entry_date >= $_POST['start_date'] && $time_entry_date <= $final_date) {
                                        //Project setting
                                        $projectsMap[$totalTimeProjects['projects'][0]['id']]['id'] = $totalTimeProjects['projects'][0]['id'];
                                        $projectsMap[$totalTimeProjects['projects'][0]['id']]['name'] = $totalTimeProjects['projects'][0]['name'];
                                        @$projectsMap[$totalTimeProjects['projects'][0]['id']]['total-hours'] += (float)$time_entries['hours'];
                                        //Client setting
                                        $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['id'] = $totalTimeTasklists['projects'][0]['tasklist']['id'];
                                        $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['name'] = $totalTimeTasklists['projects'][0]['tasklist']['name'];
                                        @$projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['total-hours'] += (float)$time_entries['hours'];
                                        //ProjectBee setting
                                        $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['projectsBee'][$tasks['id']]['id'] = $tasks['id'];
                                        $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['projectsBee'][$tasks['id']]['name'] = $tasks['content'];
                                        @$projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['projectsBee'][$tasks['id']]['total-hours'] += (float)$time_entries['hours'];
                                        //Task setting
                                        $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['id'] = $childtasks['id'];
                                        $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['name'] = $childtasks['name'];
                                        @$projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['total-hours'] += (float)$time_entries['hours'];

                                        //Time entry setting
                                        $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['time-entries'][$time_entries['person-id']]['person-id'] = $time_entries['person-id'];
                                        $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['time-entries'][$time_entries['person-id']]['name'] = $time_entries['person-first-name'];
                                        $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['time-entries'][$time_entries['person-id']]['last-name'] = $time_entries['person-last-name'];
                                        @$projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['projectsBee'][$tasks['id']]['tasks'][$childtasks['id']]['time-entries'][$time_entries['person-id']]['total-hours'] += (float)$time_entries['hours'];
                                    }
                                }
                            }
                        }
                    }

                    if($totalhoursprojectbee!=0){ //This means the ProjectBee has time logged in but for a specific task
                        $projectBeeTimeEntries = json_decode($API->getAllTimeEntriesForTask($tasks['id']), true);
                        foreach ($projectBeeTimeEntries['time-entries'] as $pB_time_entries){
                            $pb_time_entry_date = substr($pB_time_entries['date'],0,10);
                            if ($pb_time_entry_date >= $_POST['start_date'] && $pb_time_entry_date <=$final_date){
                                //Project setting
                                $projectsMap[$totalTimeProjects['projects'][0]['id']]['id'] = $totalTimeProjects['projects'][0]['id'];
                                $projectsMap[$totalTimeProjects['projects'][0]['id']]['name'] = $totalTimeProjects['projects'][0]['name'];
                                //Client setting
                                $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['id'] = $totalTimeTasklists['projects'][0]['tasklist']['id'];
                                $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['name'] = $totalTimeTasklists['projects'][0]['tasklist']['name'];
                                //ProjectBee setting
                                $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['projectsBee'][$tasks['id']]['id'] = $tasks['id'];
                                $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['projectsBee'][$tasks['id']]['name'] = $tasks['content'];


                                $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['projectsBee'][$tasks['id']]['time-entries'][$pB_time_entries['person-id']]['person-id'] = $pB_time_entries['person-id'];
                                $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['projectsBee'][$tasks['id']]['time-entries'][$pB_time_entries['person-id']]['name'] = $pB_time_entries['person-first-name'];
                                $projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['projectsBee'][$tasks['id']]['time-entries'][$pB_time_entries['person-id']]['last-name'] = $pB_time_entries['person-last-name'];
                                @$projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['projectsBee'][$tasks['id']]['time-entries'][$pB_time_entries['person-id']]['total-hours'] += (float)$pB_time_entries['hoursDecimal'];
                                @$projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['projectsBee'][$tasks['id']]['total-hours'] += (float)$pB_time_entries['hoursDecimal'];
                                @$projectsMap[$totalTimeProjects['projects'][0]['id']]['tasklists'][$totalTimeTasklists['projects'][0]['tasklist']['id']]['total-hours'] += (float)$pB_time_entries['hoursDecimal'];
                                @$projectsMap[$totalTimeProjects['projects'][0]['id']]['total-hours'] += (float)$pB_time_entries['hoursDecimal'];
                            }
                        }
                    }
                }
            }



        }
        //File creation

        /*$f = fopen("../Output/Output.csv", "r+");
        if ($f!==false){
            ftruncate($f, 0);
            fclose($f);
        }*/

        //unlink @

        @unlink("src/Output/Output.csv");
        if (!empty($projectsMap)) {

            $dataInicial = array('', $_POST['start_date'], $final_date, 'FILTRES:');

            $columnes = array('ProjecteGran', 'Client', 'Projecte', 'Tasca', 'Treballador');

            $delimeter = ';';

            $f = fopen('src/Output/Output.csv', 'w');

            fputcsv($f, $dataInicial, $delimeter);
            fputcsv($f, $columnes, $delimeter);

            $firstproject = true;
            $firsttask = true;

            foreach ($projectsMap as $projects){
                $firstproject = true;
                foreach ($projects['tasklists'] as $clients){
                    $counterProjects = 1;
                    foreach ($clients['projectsBee'] as $projectsBee){
                        if (!empty($projectsBee['tasks'])) {
                            foreach ($projectsBee['tasks'] as $tasks) {
                                $counterTimeEntry = 1;
                                foreach ($tasks['time-entries'] as $timeentry) {
                                    $fila = array($projects['name'], $clients['name'], $projectsBee['name'], $tasks['name'], $timeentry['name'], '', $timeentry['total-hours'] . 'h');

                                    if (!$firsttask) {
                                        $fila[2] = '';
                                        $fila[3] = '';
                                    }
                                    if ($counterTimeEntry === count($tasks['time-entries'])) $fila = array($projects['name'], $clients['name'], $projectsBee['name'], $tasks['name'], $timeentry['name'], '', $timeentry['total-hours'] . 'h', 'TOTAL tasca' . $tasks['name'], $tasks['total-hours'] . 'h');

                                    if (!$firstproject) $fila[0] = '';

                                    fputcsv($f, $fila, $delimeter);

                                    if ($counterTimeEntry === count($tasks['time-entries'])) {
                                        $auxfila = array('', $clients['name'], '', '', 'TOTAL ' . $projectsBee['name'], '', $projectsBee['total-hours'] . 'h');
                                        fputcsv($f, $auxfila, $delimeter);
                                        $filaBlank = array('', $clients['name']);
                                        fputcsv($f, $filaBlank, $delimeter);
                                    }

                                    $counterTimeEntry++;
                                    $firsttask = false;
                                    $firstproject = false;
                                }
                                $firsttask = true;
                            }
                            if ($counterProjects === count($clients['projectsBee'])) {
                                $auxfila = array('', $clients['name'], '', '', 'TOTAL ' . $clients['name'], '', $clients['total-hours'] . 'h');
                                fputcsv($f, $auxfila, $delimeter);
                                /*$filaBlank = array('', $clients['name']);
                                fputcsv($f, $filaBlank, $delimeter);*/
                            }
                            $counterProjects++;
                        }
                    }

                }

            }


            /*foreach ($data as $fields){
                fputcsv($f, $fields,';');
            }*/
            fclose($f);

            //file_put_contents("Output.csv", '');
        }
    }
}
else {
    include_once __DIR__.'\../Resources/r_404.php';
}





//print($file);
