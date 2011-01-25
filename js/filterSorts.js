// Grays out disabled menu options
function grayout(e) { 
  for (var i=0, option; option = e.options[i]; i++) { 
    if (option.disabled) { 
      option.style.color = "graytext"; 
    }
    else 
    { 
      option.style.color = "menutext"; 
    } 
  } 
}

// Enables/disables sort options
function toggleOpts(which,e) {
  var val;
  for (var i=0, option; option = e.options[i]; i++) { 
    if (which=='bat') {
      if ((option.value=='stuff')||(option.value=='control')||(option.value=='movement')||(option.value=='velocity')||(option.value=='stamina')) {
        option.disabled='disabled';
      }
      else 
      {
        option.disabled='';
      }
    }
    if (which=='pit') {
      if ((option.value=='contact')||(option.value=='gap')||(option.value=='power')||(option.value=='eye')||(option.value=='strikeouts')||(option.value=='speed')||(option.value=='stealing')||(option.value=='baserunning')) {
        option.disabled='disabled';
      }
      else {
        option.disabled='';
      }
    }
  } 
}

// Correct sorts based on who is selected to display
function whoChange() {
    var whoElem=document.getElementById('who');
    var sort1=document.getElementById('sort1');
    var sort2=document.getElementById('sort2');
    var sort3=document.getElementById('sort3');
    var whoVal=whoElem[whoElem.selectedIndex].value;
    var sort1Val=sort1[sort1.selectedIndex].value;
    var sort2Val=sort1[sort2.selectedIndex].value;
    var sort3Val=sort1[sort3.selectedIndex].value;

    if ((whoVal!='batters')&&(whoVal!='pitchers')) {whoVal=parseInt(whoVal);}

    if ((whoVal=='batters')||((whoVal>1)&&(whoVal<=10))) {
      if ((sort1Val=='stuff')||(sort1Val=='control')||(sort1Val=='movement')||(sort1Val=='velocity')||(sort1Val=='stamina')) {
        sort1.value='contact';
      }
      if ((sort2Val=='stuff')||(sort2Val=='control')||(sort2Val=='movement')||(sort2Val=='velocity')||(sort2Val=='stamina')) {
        sort2.value='power';
      }
      if ((sort3Val=='stuff')||(sort3Val=='control')||(sort3Val=='movement')||(sort3Val=='velocity')||(sort3Val=='stamina')) {
        sort3.value='eye';
      }

      toggleOpts('bat',sort1);
      toggleOpts('bat',sort2);
      toggleOpts('bat',sort3);
    }

    if ((whoVal=='pitchers')||(whoVal>10)) {
      if ((sort1Val=='contact')||(sort1Val=='gap')||(sort1Val=='power')||(sort1Val=='eye')||(sort1Val=='strikeouts')||(sort1Val=='speed')||(sort1Val=='stealing')||(sort1Val=='baserunning')) {
        sort1.value='stuff';
      }
      if ((sort2Val=='contact')||(sort2Val=='gap')||(sort2Val=='power')||(sort2Val=='eye')||(sort2Val=='strikeouts')||(sort2Val=='speed')||(sort2Val=='stealing')||(sort2Val=='baserunning')) {
        sort2.value='control';
      }
      if ((sort3Val=='contact')||(sort3Val=='gap')||(sort3Val=='power')||(sort3Val=='eye')||(sort3Val=='strikeouts')||(sort3Val=='speed')||(sort3Val=='stealing')||(sort3Val=='baserunning')) {
        sort3.value='movement';
      }
      toggleOpts('pit',sort1);
      toggleOpts('pit',sort2);
      toggleOpts('pit',sort3);
    }

    grayout(sort1);
    grayout(sort2);
    grayout(sort3);
}


