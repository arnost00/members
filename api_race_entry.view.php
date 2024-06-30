<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Účast na závodě</title>
  <link rel="stylesheet" href="./js/jquery-ui.css">
  <style>

    .ui-selectmenu-button.ui-button {
        width: 85%;
    }

    .active {
        background-color: darkorange;
    }

    .hidden {
        display: none;
    }

    div.ui-accordion > h3 {
	    min-height: 80px;
    }

    span.toolbar > button {
        min-height: 60px;
    }

    button.uncheckAll {
        float: right;
    }

  </style>

  <script src="./js/jquery-3.7.1.min.js"></script>
  <script src="./js/jquery-ui.min.js"></script>
  <script>
  $( function() {

    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    var id_race = urlParams.get('race_id');
    // document.getElementById("title").innerHTML = "Zavod "+id_race;
    var dat = [];
    var accordion = document.getElementById("accordion");
    $.getJSON('api_race_entry.php?id_race='+id_race, function(data) {
        $( "#accordion" ).html('');
        data.forEach(el => {
            var flds_entry = '<label for="checkbox-1-'+el.id_user+'">Přihlášen</label><input type="checkbox" name="checkbox-1-'+el.id_user+'" id="checkbox-1-'+el.id_user+'"><label for="checkbox-2-'+el.id_user+'">Účast</label><input type="checkbox" name="checkbox-2-'+el.id_user+'" id="checkbox-2-'+el.id_user+'">';
            var flds_kat = '<select id="cat-'+el.reg+'" class="select-cat"></select>';
            var flds_note = '<input type="text" value="sem napiš poznámku ..."></input>';
            
            // tlacitka na prihlaseni a ucast v headru accordionu
            var head_span_prihlasen = '<button id="btnEntry-'+el.id_user+'" style="margin-right:2px;" onClick="tickEntry(this,\''+el.id_user+'\','+id_race+')" '+(el.add_by_fin==1?'class="active"':'class=""')+'>Přihl.</button>';
            var head_span_ucast = '<button id="btnParticipate-'+el.id_user+'" onClick="tickParticipate(this,\''+el.id_user+'\','+id_race+')" '+(el.participated==1?'class="active"':'class=""')+' '+((el.id)?'':'hidden')+'>Účast</button>';
            var head_span = '<span style="float:right;" class="toolbar ui-widget-header ui-corner-all">'+((el.add_by_fin!=1&&el.id)?'':head_span_prihlasen)+head_span_ucast+'</span>';

            $( accordion ).append('<h3 id='+el.id_user+'>'+el.reg+'::'+el.name+' '+head_span+'</h3><div id="div-'+el.reg+'"></div>');
            var div_entry_data = $( "#div-"+el.id_user);
            $( div_entry_data ).append(flds_note).append(flds_kat).append(flds_entry);
            if(el.kat) $( "#checkbox-1-"+el.id_user).prop("checked", true );
            var sel_cat = $( "#cat-"+el.id_user );
            var arr_cat = ['vyber kategorii','h21','d21'];
            arr_cat.forEach(el_cat => {
                $( sel_cat ).append('<option id="cat-opt-'+el.id_user+'-'+el_cat+'" value="'+el_cat+' selected">'+el_cat+'</option>');
            });
        });
        $( accordion ).accordion("refresh");

        $( "input:checkbox" ).checkboxradio({
            icon: false
        });

    });

    $( accordion ).accordion({
        active: false,
        collapsible: true,
        heightStyle: "content"
    });

    $("#search").keyup(function(){
        var searchedText = $('#search').val().toString().toLowerCase();
        $( "h3" ).each(function(){
            var htxt=$(this).text().toString().toLowerCase();
            if (htxt.indexOf(searchedText) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    $(document).on('click', '#btnAccomodation', function() {
        $.getJSON('api_race_entry.php?id_race='+id_race+'&action=accomodation', function(data) {
            console.log('accomodation|id_race:'+id_race+'|time:'+new Date().getTime());
        }).done(function(result) {
            let accomodation = document.getElementById("divAccomodation");
            let entry = document.getElementById("divEntry");
            let btnSave = document.getElementById("btnSave");
            document.getElementById("accomodation").innerHTML = result.join("<br/>");

            $( accomodation ).removeClass("hidden");
            $( entry ).addClass("hidden");

            $( btnSave).addClass("ui-button ui-widget ui-corner-all");
            console.log('accomodation|id_race:'+id_race+'|result:'+result+'|time:'+new Date().getTime());
        })
    });

    $(document).on('click', '#btnEntry', function(){
        let accomodation = document.getElementById("divAccomodation");
        let entry = document.getElementById("divEntry");

        $( accomodation ).addClass("hidden");
        $( entry ).removeClass("hidden");
    });

    $(document).on('click', '#uncheckAll', function() {
        $.getJSON('api_race_entry.php?id_race='+id_race+'&action=uncheckAll', function(data) {
            console.log('uncheckall|id_race:'+id_race+'|time:'+new Date().getTime());
        }).done(function(result) {
            location.reload();
        });
    });


  }); // end of inner function on document ready
  
  function tickParticipate(elem, id_user, id_race) {
    event.stopPropagation(); // this is
    event.preventDefault(); // the magic
    $.getJSON('api_race_entry.php?id_race='+id_race+'&action=participate&id_user='+id_user, function(data) {
        console.log('participate|user:'+id_user+'|id_race:'+id_race+'|result:'+data);
    }).done(function(result) {
        if (result > 0) {
            $( elem ).toggleClass("active");
        }
    });
  }
    
  function tickEntry(elem, id_user, id_race) {
    event.stopPropagation(); // this is
    event.preventDefault(); // the magic
    $.getJSON('api_race_entry.php?id_race='+id_race+'&action=entryByFin&id_user='+id_user, function(data) {
        console.log('entryByFin|id_user:'+id_user+'|id_race:'+id_race+'|result:'+data);
    }).done(function(result) {
        refresh(id_race, id_user, elem);
    })
  };

  function refresh(id_race, id_user, elem) {
    $.getJSON('api_race_entry.php?id_race='+id_race+'&action=detail&id_user='+id_user, function(data) {
        console.log('entryByFin|id_user:'+id_user+'|id_race:'+id_race+'|result:'+data);
    }).done(function(result) {
        if (result == null) {
            //user deleted
            document.getElementById('btnEntry-'+id_user).classList.remove("active");
            document.getElementById('btnParticipate-'+id_user).classList.remove("active");
            document.getElementById('btnParticipate-'+id_user).hidden=true;
        } else {
            //user updated
            (result.participated == 1) ? document.getElementById('btnParticipate-'+id_user).classList.add("active") : document.getElementById('btnParticipate-'+id_user).classList.remove("active");
            document.getElementById('btnEntry-'+id_user).classList.add("active");
            document.getElementById('btnParticipate-'+id_user).hidden=false;

        }
    })
  };

  function generateAccomodation(id_race) {
    $.getJSON('api_race_entry.php?id_race='+id_race+'&action=accomodation', function(data) {
        console.log('accomodation||id_race:'+id_race);
    }).done(function(result) {
        console.log('accomodation||id_race:'+id_race+'|result:'+result);
    })
  };

  function saveDataToFile(text) {
    // Get the data from each element on the form.
    const data = text;        
    // Convert the text to BLOB.
    const textToBLOB = new Blob([data], { type: 'text/plain' });
    const sFileName = 'ubytovani.txt'; // The file to save the data.

    let newLink = document.createElement("a");
    newLink.download = sFileName;

    if (window.webkitURL != null) {
        newLink.href = window.webkitURL.createObjectURL(textToBLOB);
    }
    else {
        newLink.href = window.URL.createObjectURL(textToBLOB);
        newLink.style.display = "none";
        document.body.appendChild(newLink);
    }

    newLink.click(); 
  }
  
  </script>
</head>
<body>
<?
    require_once ('connect.inc.php');
    db_Connect();
    $race_id = $_GET['race_id'];
    $query = "select nazev from ".TBL_RACE." where id = $race_id";
    @$vysledek=$db_conn->query($query);
    $zaznam=mysqli_fetch_array($vysledek)
?>
<h2 id='title'>Závod <?=$zaznam['nazev']?></h2>
<button id="btnAccomodation" class="ui-button ui-widget ui-corner-all">Zobraz ubytované</button> <---||---> <button id="btnEntry" class="ui-button ui-widget ui-corner-all">Zobraz seznam</button>
<button id="uncheckAll" class="ui-button ui-widget ui-corner-all uncheckAll">Odškrtni účast u všech</button>
<hr/>

<div id="divAccomodation" class="hidden">
    <p id="btnSave" onclick="saveDataToFile(document.getElementById('accomodation').innerText);">Ulož</p>
    <div id="accomodation"></div>
</div>

<div id="divEntry" class="">
    <input id="search" placeholder="filtr podle jmena"/><br/><br/>
    <div id="accordion">
        Načítám data závodu ...
    </div>
</div>

</body>
</html>