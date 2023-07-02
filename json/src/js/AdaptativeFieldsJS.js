function onSelectProjectChangeClient(){


    var project = document.getElementById("projects").value;
    var xmlhttp = new XMLHttpRequest();

    var user = localStorage.getItem('email');
    var pass = localStorage.getItem('pass');

    var options = '<option value="0">Tots</option>';

    if (project=="0"){ //Tots els clients
        var url = "https://beedata.teamwork.com/tasklists.json";


    }
    else //Projecte seleccionat -> Tasklists (clients) d'aquell projecte
    {
        var url = 'https://beedata.teamwork.com/projects/' + project + '/tasklists.json';

    }
    xmlhttp.onreadystatechange = function (){
        if (this.readyState == 4 && this.status == 200){
            var projectArrayJSON = JSON.parse(this.response);
            for (var tasklist of projectArrayJSON['tasklists']){
                options += '<option value="' + tasklist['id'] + '">' + tasklist['name'] + '</option>';
            }

            document.getElementById("tasklist").innerHTML = options;
        }
    }
    xmlhttp.open("GET", url, true);
    xmlhttp.setRequestHeader('Authorization', 'Basic ' + btoa( user+":"+pass));
    xmlhttp.send();

}