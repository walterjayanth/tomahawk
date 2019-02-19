<!DOCTYPE html>
<html xmlns="https://www.w3.org/1999/xhtml">
<head>
<title>Rooms Booking</title>
<meta name="description" content="Book Rooms">

<meta http-equiv="content-type" content="text/html;charset=ISO-8859-1"/>
<meta http-equiv="X-UA-Compatible" content="IE=9"/>
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>

<link rel="stylesheet" type="text/css" href="css/app.css">

<link rel="stylesheet" href="css/SalsaCalendar.min.css">
<script src="js/SalsaCalendar.min.js"></script>

<link rel="stylesheet" href="css/datepickk.css">
<script src="js/datepickk.js"></script>


</head>
<body>

<div id="canvas">

<table>
<tr>
<td>

<div class="box">
  <div class="heading">Check Rooms</div>
  <label for="adate">Your Arrival Date:</label>
  <input id="adate" name="adate" class="salsa-calendar-input" size="7" readonly="readonly">
  <br><br>
  <label for="gnum">Number of Guests:</label>
  <input id="gnum" name="gnum" size="2" maxlength="2" value="1">
  <br><br>
  <input id="checkrooms" name="submit" type="button" value="Check Rooms">
</div>


<div id="cal" class="box" style="display:none">
  <div class="heading">Rooms Availability</div><br>
    <input id="pTDate" name="pTDate" type="hidden" value="">
    <div id="DP" style="height:460px;width:100%;max-width:560px;"></div>
    <script>
      var now = new Date();
      var DP = new Datepickk({
        container: document.querySelector('#DP'),
        inline:true,
        range: false,
        closeOnSelect: false,

        tooltips: {
          date: new Date(2019,14,2),
          text: 'Room Name: Room1, Room2'
        }
      });
      DP.startDate = now;
      DP.minDate = now.setDate(now.getDate() - 1);
    </script>
    <br>
    <input id="showsum" type="button" value="View Summary" style="display:none">
</div>

</td>
<td>

<div id="sum" class="box" style="display:none">
    <div class="heading">Summary</div>
    <div id="summary"></div>
    <br>
    <input id="showenq" type="button" value="Enquire">
</div>

<div id="enq" class="box" style="display:none">
    <div class="heading">Enquiry Form</div>
    <form action="output.php" target="_self" method="POST">
      <label for="fname">First Name:</label>
      <input id="fname" name="fname" size="25" maxlength="50">
      <br><br>
      <label for="lname">Last Name:</label>
      <input id="lname" name="lname" size="25" maxlength="50">
      <br><br>
      <label for="email">Email:</label>
      <input id="email" name="email" size="30" maxlength="50">
      <br><br>
      <input id="hroom" type="hidden" name="room" value="">
      <input id="htotal" type="hidden" name="total" value="">
      <input id="hdates" type="hidden" name="seldates" value="">
      <input id="subenq" name="submit" type="submit" value="Enquire Now">
    </form>
</div>

</td>
</tr>
</table>
<?php



?>

</div>

<script language="javascript">
function ajaxGET(url,funC){var A=new XMLHttpRequest();A.onreadystatechange=function(){if (A.readyState==4 && A.status==200) funC(A.responseText);};A.open("GET",url,true);A.send();}
function E$(id){return document.getElementById(id);}

//Date Picker Calendar Initialization
var calendar_from = new SalsaCalendar({
    inputId: 'adate',
    lang: 'en',
    range: {
      min: 'today'
    },
    calendarPosition: 'right',
    fixed: false,
    connectCalendar: true
});


var p={}; //Property Object
var Rates={}; //Rates Object For Total Cost Calculation
var avRooms={}; //Available Rooms Object
var Rooms={}; //Rooms Object
var selDate=[]; //Selected Dates Array
var pRates,totRates;

//Check Available Rooms From rooms.json Via Ajax Request
E$('checkrooms').onclick=function(){
  E$('cal').style.display='inline-block';

  ajaxGET('rooms.json',function(res){

  try{
    if (res=="" || res=='null') alert("No Rooms Available !");
    else p=JSON.parse(res);

    var pRooms=p['rooms'],totRooms=pRooms.length;
    pRates=p['rates'];
    totRates=pRates.length;
    var pAv=p['availability'],totAv=pAv.length;
    var adate=new Date(E$('adate').value),tooltip={};
    var totGuest=E$('gnum').value;


    for(i=0;i<totRooms;i++){
      Rooms[pRooms[i]['name']]={};
      Rooms[pRooms[i]['name']]['bedrooms']=pRooms[i]['bedrooms'];
      Rooms[pRooms[i]['name']]['maxguests']=pRooms[i]['maxguests'];
      Rooms[pRooms[i]['name']]['tax-inclusive']=pRooms[i]['tax-inclusive'];
    }


    for(d=0;d<7;d++){
      var cdate=new Date(),curdt;
      cdate.setDate(adate.getDate() + d);
      curdt=formatDate(cdate);
      avRooms[curdt]={};

      for(i=0;i<totAv;i++){
        var room_name=pAv[i]['room_name'];
        var start_date=new Date(pAv[i]['start_date']);
        var end_date=new Date(pAv[i]['end_date']);
        var avail=pAv[i]['available'];

        if (cdate.getTime()>=start_date.getTime() && cdate.getTime()<=end_date.getTime()){
          if (avail && totGuest<=Rooms[room_name]['maxguests']){
            if (tooltip[d]!==undefined){
              if (tooltip[d].indexOf(room_name)<0) tooltip[d]+=', ' + room_name;
            }
            else tooltip[d]=room_name;

            DP.tooltips={date: cdate, text: "Available Rooms: "+tooltip[d]};
            avRooms[curdt][room_name]=true;

            Rates[room_name]={};
            Rates[room_name]['rate']=0;
            Rates[room_name]['tax']=0;
            Rates[room_name]['tot']=0;
          }
        }
      }


    }



  }catch(e){
    alert(e);
  }

  });

}


//Show Button to Display Summary
DP.onSelect = function (checked){
  if (DP.selectedDates!='') E$('showsum').style.display='inline-block';
  else E$('showsum').style.display='none';
};


//Show Booking Summary
E$('showsum').onclick=function(){
  E$('sum').style.display='inline-block';

  var summary='',rates='';
  var totD=DP.selectedDates.length;

  for(r=0;r<totRates;r++){
    Rates[pRates[r]['room_name']]['rate']=0;
    Rates[pRates[r]['room_name']]['tax']=0;
    Rates[pRates[r]['room_name']]['tot']=0;
  }

  for(i=0;i<totD;i++){
    var dt=new Date(DP.selectedDates[i]);
    selDate[i]=formatDate(dt);
    summary+='<br>'+selDate[i];

    for (var room_name in avRooms[selDate[i]]) {
      if (avRooms[selDate[i]].hasOwnProperty(room_name)){

        for(r=0;r<totRates;r++){
          var start_date=new Date(pRates[r]['start_date']);
          var end_date=new Date(pRates[r]['end_date']);
          if (room_name==pRates[r]['room_name'] && dt.getTime()>=start_date.getTime() && dt.getTime()<=end_date.getTime()){

            Rates[room_name]['rate']+=pRates[r]['rate'];
            if (Rooms[room_name]['tax-inclusive']==true){
              Rates[room_name]['tax']+=0;
              Rates[room_name]['tot']+=pRates[r]['rate'];
            }else{
              Rates[room_name]['tax']+=pRates[r]['rate']*15/100;
              Rates[room_name]['tot']+=pRates[r]['rate']*115/100;
            }
          }
        }
      }
    }
  }

  for (var room_name in Rates) {
    if (Rates.hasOwnProperty(room_name)){
      rates+='<input name="rooms" type="radio" value="'+room_name+'" checked> <b><u>'+room_name+'</u></b><br>Room Charges: NZD$'+ Rates[room_name]['rate']
          +'<br>Tax: NZD$'+ Rates[room_name]['tax']
          +'<br>Total Charges: NZD$'+ Rates[room_name]['tot']+'<br><br><br>';
    }
  }
  E$('summary').innerHTML='Selected Dates:<br>'+summary+'<br><br>Available Rooms:<br><br>'+rates;

}


//Show Enquiry Form
E$('showenq').onclick=function(){
  E$('enq').style.display='inline-block';

  var rooms = document.getElementsByName('rooms');
  var room_name;
  for(var i = 0; i < rooms.length; i++){
      if(rooms[i].checked){
          room_name = rooms[i].value;
      }
  }

  E$('hroom').value=room_name;
  E$('htotal').value=Rates[room_name]['tot'];
  E$('hdates').value=selDate.join(',');
}


function formatDate(dt){
  return dt.getFullYear()+'-'+("0"+(dt.getMonth()+1)).slice(-2)+'-'+("0" + dt.getDate()).slice(-2);
}
</script>
</body>
</html>