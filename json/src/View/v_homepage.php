<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <link rel="stylesheet" href="../style/style.css">
    <script src="../js/AdaptativeFieldsJS.js"></script>
    <link href='https://unpkg.com/css.gg@2.0.0/icons/css/log-out.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>
<body>
<div class="sidenav">
    <a href="?action=logout"><i class="gg-log-out" style="margin-right: 16px;display: inline-block;margin-left: 0px;"></i>Logout</a>
</div>
<div class="main">
    <h1>Hola!</h1>
    <form method="post" action="?action=homepage">
        <div class="inline field-container">
            <div class="marginbottom5">
                <label class="label-home text" for="projects">Projecte</label>
            </div>
            <select class="select-home pointer" name="projects" id="projects" onchange="onSelectProjectChangeClient()">
                <option value="0">Tots</option>
                <?php
                foreach ($result['projects'] as $key => $value) {
                ?> <option value="<?php echo $value['id']?>">  <?php echo $value['name']?></option>
                <?php }
                ?>
            </select>
        </div>
        <div class="inline field-container">
            <div class="marginbottom5">
                <label class="label-home text" for="tasklist">Client</label>
            </div>
            <select class="select-home pointer" name="tasklist" id="tasklist">
                <option value="0">Tots</option>
                <?php
                foreach ($tasklistjson['tasklists'] as $key => $value) {
                    ?> <option value="<?php echo $value['id']?>">  <?php echo $value['name']?></option>
                <?php }
                ?>
            </select>
        </div>
        <div class="inline field-container">
            <div class="marginbottom5">
                <label class="label-home text" for="start_date">Data inicial</label>
            </div>
            <input class="input-home pointer" type="date" id="start_date" name="start_date">
        </div>
        <div class="inline field-container">
            <div class="marginbottom5">
                <label class="label-home text" for="final_date">Data final</label>
            </div>
            <input class="input-home pointer" type="date" id="final_date" name="final_date">
        </div>
        <div class="inline" id="containerBtnSearch">
            <i class="fa fa-search pointer" style="position: relative; left: 28px;"></i>
            <input class="inline pointer" type="submit" value="Cerca" id="btnCerca">
        </div>
    </form>




    <?php  if ($_SERVER['REQUEST_METHOD'] === 'POST'){ ?>
        <?php if(!empty($projectsMap)){?>

            <?php foreach ($projectsMap as $projects){?>

                    <button class="accordion2">
                        <div class="inline">Project: <?php echo @$projects['name']?> </div> <div class="inline width80prc"></div> <div class="inline relativeRightH"> Total Hores: <?php echo @$projects['total-hours']?></div>
                    </button>
                <div class="panel2 marginleft4prc">

                <?php foreach ($projects['tasklists'] as $clients){?>
                        <button class="accordion2">
                            <div class="inline maxwidth20">Client: <?php echo @$clients['name']?></div> <div class="inline width57prc"></div> <div class="inline">Total Hores: <?php echo @$clients['total-hours']?></div>
                        </button>
                        <div class="panel2 marginleft4prc">
                    <?php foreach ($clients['projectsBee'] as $projectsBee){?>
                            <button class="accordion2">
                                <div class="inline maxwidth20">Projecte: <?php echo @$projectsBee['name']?></div class="inline"> <div class="inline width57prc"></div> <div class="inline">Total Hores: <?php echo @$projectsBee['total-hours']?></div>
                            </button>
                            <div class="panel2 marginleft4prc">
                        <?php if(!empty($projectsBee['time-entries'])){
                            foreach ($projectsBee['time-entries'] as $pbtimeentry){?>
                                <div>
                                    <div class="inline maxwidth20"><?php echo @$pbtimeentry['name']?> <?php echo @$pbtimeentry['last-name']?></div><div class="inline width57prc"></div><div class="inline"> <?php echo @$pbtimeentry['total-hours']?> Hores</div>
                                </div>
                                <hr>
                            <?php }
                        }?>
                        <?php if (!empty($projectsBee['tasks'])){
                        foreach (@$projectsBee['tasks'] as $tasks){?>
                                <button class="accordion2">
                                    <div class="inline maxwidth20">Tasca: <?php echo @$tasks['name']?></div> <div class="inline width57prc"> </div> <div class="inline"> Total Hores: <?php echo @$tasks['total-hours']?></div>
                                </button>
                                <div class="panel2 marginleft4prc">
                            <?php if(!empty($tasks['time-entries'])){
                                foreach ($tasks['time-entries'] as $timeentry){?>
                                        <div>
                                            <div class="inline maxwidth20"> <?php echo @$timeentry['name']?> <?php echo @$timeentry['last-name']?></div><div class="inline width57prc"> </div> <div class="inline"> <?php echo @$timeentry['total-hours']?> Hores</div>
                                        </div>
                                        <hr>

                                <?php   }?>
                                </div>
                                <?php }

                            }
                        }?>
                            </div>
                        <?php

                      }?>
                    </div>
                   <?php }?>
                </div>
             <?php }?>
            <a id="btnDownload" href="../Output/Output.csv">Download</a>
        <?php }
        else{?>
            <div id="dvNoResult"> No s'han trobat resultats per aquests filtres de cerca</div>

        <?php }
    }?>



</div>
</body>
</html>

<script type="text/javascript">
    $('#start_date').on('input', function() {
        $('#containerBtnSearch').css('display', $(this).val()  !== '' ? 'inline-block' : 'none')
    });

    /*var acc = document.getElementsByClassName("accordion");
    var i;

    for (i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function () {
            /* Toggle between adding and removing the "active" class,
            to highlight the button that controls the panel

            var panel = this.nextElementSibling;
            this.classList.toggle("active");

            var activepannels = document.getElementsByClassName("active");

            var y;

            if (panel.style.maxHeight) {
                panel.style.maxHeight = null;
            } else {
                panel.style.maxHeight = panel.scrollHeight + "px";

                /*for (y = 0; y < activepannels.length; y++){
                    activepannels[y].nextElementSibling.style.maxHeight = activepannels[y].scrollHeight + "px";
                }
            }


        });
    }*/

    var acco = document.getElementsByClassName("accordion2");
    var j;

    for (j = 0; j < acco.length; j++) {
        acco[j].addEventListener("click", function () {
            /* Toggle between adding and removing the "active" class,
            to highlight the button that controls the panel */
            this.classList.toggle("active");

            /* Toggle between hiding and showing the active panel */
            var panel = this.nextElementSibling;
            if (panel.style.display === "block") {
                panel.style.display = "none";
            } else {
                panel.style.display = "block";
            }
        });
    }



</script>
