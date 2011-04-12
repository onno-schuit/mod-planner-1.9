/////////////////////////////////////////////////////////////////////////////////////
// Modified and enhanced by:
//      Onno Schuit - http://solin.eu
//
//      Nathaniel Brown - http://nshb.net
//      Email: nshb@inimit.com
//
// Created by: 
//      Simon Willison - http://simon.incutio.com
//
// License:
//      GNU Lesser General Public License version 2.1 or above.
//      http://www.gnu.org/licenses/lgpl.html
//
/////////////////////////////////////////////////////////////////////////////////////

// arrays for month and weekday names are derived from calendar-{en|nl|..|de}.js
var monthNames = Calendar._MN;
var weekdayNames = Calendar._DN;

/* Array of objects, each has 're', a regular expression and 'handler', a 
   function for creating a date from something that matches the regular 
   expression. Handlers may throw errors if string is unparseable. 
*/
var defaultDateParsePatterns = [
    // Today
    {   re: re_now,
        handler: function() { 
            return new Date();
        } 
    },
    // Tomorrow
    {   re: re_tomorrow,
        handler: function() {
            var d = new Date(); 
            d.setDate(d.getDate() + 1); 
            return d;
        }
    },
    // Yesterday
    {   re: re_yesterday,
        handler: function() {
            var d = new Date();
            d.setDate(d.getDate() - 1);
            return d;
        }
    },                
    // +5 (5 days from now)
    {   re: /^\+(\d{1,2})/i, 
        handler: function(bits) {                        
            current_date = new Date();
            var dd = current_date.getDate();
            current_date.setDate(dd + parseInt(bits[1]));
            return current_date;            
        }
    }, 
    // -5 (5 days ago)
    {   re: /^-(\d{1,2})/i, 
        handler: function(bits) {                        
            current_date = new Date();
            var dd = current_date.getDate();
            current_date.setDate(dd - parseInt(bits[1]));
            return current_date;           
        }
    },        
    // mm/dd/yyyy (American style)
    {   re: /(\d{1,2})\/(\d{1,2})\/(\d{4})/,
        handler: function(bits) {
            // if calendarIfFormat is set to another format, use that instead                        
            pattern = /%d.%m.*/i; // determine if order is day/month or month/day           
            if (pattern.test(calendarIfFormat)) {               
              var yyyy = parseInt(bits[3], 10);
              var dd = parseInt(bits[1], 10);
              var mm = parseInt(bits[2], 10) - 1;
            } else {                     
              var yyyy = parseInt(bits[3], 10);
              var dd = parseInt(bits[2], 10);
              var mm = parseInt(bits[1], 10) - 1;
            }

            if ( DateInRange( yyyy, mm, dd ) )
               return getDateObj(yyyy, mm, dd);
        }
    },
    // mm/dd/yy (American style) short year
    {   re: /(\d{1,2})\/(\d{1,2})\/(\d{1,2})/,
        handler: function(bits) {
            var d = new Date();
            var yyyy = d.getFullYear() - (d.getFullYear() % 100) + parseInt(bits[3], 10);          
          
            // if calendarIfFormat is set to another format, use that instead                        
            pattern = /%d.%m.*/i; // determine if order is day/month or month/day            
            if (pattern.test(calendarIfFormat)) {
              var dd = parseInt(bits[1], 10);
              var mm = parseInt(bits[2], 10) - 1;
            } else {
              var dd = parseInt(bits[2], 10);
              var mm = parseInt(bits[1], 10) - 1;              
            }

            if ( DateInRange(yyyy, mm, dd) )
               return getDateObj(yyyy, mm, dd);

        }
    },    
    // mm/dd (American style) omitted year
    {   re: /(\d{1,2})\/(\d{1,2})/,
        handler: function(bits) {

            var d = new Date();
            var yyyy = d.getFullYear();
            
            // if calendarIfFormat is set to another format, use that instead                        
            pattern = /%d.%m.*/i; // determine if order is day/month or month/day            
            if (pattern.test(calendarIfFormat)) {
              var dd = parseInt(bits[1], 10);
              var mm = parseInt(bits[2], 10) - 1;              
            } else {            
              var dd = parseInt(bits[2], 10);
              var mm = parseInt(bits[1], 10) - 1;
            }

            if ( DateInRange(yyyy, mm, dd) )
               return getDateObj(yyyy, mm, dd);
        }
    },    
    // mm-dd-yyyy
    {   re: /(\d{1,2})-(\d{1,2})-(\d{4})/,
        handler: function(bits) {
            var yyyy = parseInt(bits[3], 10);
            // if calendarIfFormat is set to another format, use that instead                        
            pattern = /%d.%m.*/i; // determine if order is day/month or month/day            
            if (pattern.test(calendarIfFormat)) {            
              var dd = parseInt(bits[1], 10);
              var mm = parseInt(bits[2], 10) - 1;
            } else {              
              var dd = parseInt(bits[2], 10);
              var mm = parseInt(bits[1], 10) - 1;
            }

            if ( DateInRange( yyyy, mm, dd ) ) {
               return getDateObj(yyyy, mm, dd);
            }

        }
    },
    // dd.mm.yyyy
    {   re: /(\d{1,2})\.(\d{1,2})\.(\d{4})/,
        handler: function(bits) {
            var yyyy = parseInt(bits[3], 10);
            // if calendarIfFormat is set to another format, use that instead                        
            pattern = /%d.%m.*/i; // determine if order is day/month or month/day            
            if (pattern.test(calendarIfFormat)) {
              var dd = parseInt(bits[1], 10);
              var mm = parseInt(bits[2], 10) - 1;              
            } else {
              var dd = parseInt(bits[2], 10);
              var mm = parseInt(bits[1], 10) - 1;              
            }            
            if ( DateInRange( yyyy, mm, dd ) )
               return getDateObj(yyyy, mm, dd);

        }
    },
    // yyyy-mm-dd (ISO style)
    {   re: /(\d{4})-(\d{1,2})-(\d{1,2})/,
        handler: function(bits) {

            var yyyy = parseInt(bits[1], 10);
            // if calendarIfFormat is set to another format, use that instead                        
            pattern = /%d.%m.*/i; // determine if order is day/month or month/day            
            if (pattern.test(calendarIfFormat)) {              
              var dd = parseInt(bits[2], 10);
              var mm = parseInt(bits[3], 10) - 1;
            } else {
              var dd = parseInt(bits[3], 10);
              var mm = parseInt(bits[2], 10) - 1;              
            }

            if ( DateInRange( yyyy, mm, dd ) )
               return getDateObj(yyyy, mm, dd);

        }
    },
    // yy-mm-dd (ISO style) short year
    {   re: /(\d{1,2})-(\d{1,2})-(\d{1,2})/,
        handler: function(bits) {

            var d = new Date();
            var yyyy = d.getFullYear() - (d.getFullYear() % 100) + parseInt(bits[1], 10);
            
            // if calendarIfFormat is set to another format, use that instead                        
            pattern = /%d.%m.*/i; // determine if order is day/month or month/day            
            if (pattern.test(calendarIfFormat)) {            
              var dd = parseInt(bits[2], 10);
              var mm = parseInt(bits[3], 10) - 1;
            } else {
              var dd = parseInt(bits[3], 10);
              var mm = parseInt(bits[2], 10) - 1;              
            }

            if ( DateInRange( yyyy, mm, dd ) )
               return getDateObj(yyyy, mm, dd);

        }
    },
    // mm-dd (ISO style) omitted year
    {   re: /(\d{1,2})-(\d{1,2})/,
        handler: function(bits) {

            var d = new Date();
            var yyyy = d.getFullYear();
            // if calendarIfFormat is set to another format, use that instead                        
            pattern = /%d.%m.*/i; // determine if order is day/month or month/day            
            if (pattern.test(calendarIfFormat)) {
              var dd = parseInt(bits[1], 10);
              var mm = parseInt(bits[2], 10) - 1;              
            } else {                                                      
              var dd = parseInt(bits[2], 10);
              var mm = parseInt(bits[1], 10) - 1;
            }

            if ( DateInRange( yyyy, mm, dd ) )
               return getDateObj(yyyy, mm, dd);

        }
    },
    
    // mon, tue, wed, thr, fri, sat, sun
    {   re: re_weekdays,
        handler: function(bits) {
            var d = new Date();
            var day = d.getDay();
            var newDay = parseWeekday(bits[1]);
            var addDays = newDay - day;
            if (newDay <= day) {
                addDays += 7;
            }
            d.setDate(d.getDate() + addDays);
            return d;
        }
    },
];

var dateParsePatterns = translatedDateParsePatterns.concat(defaultDateParsePatterns);




/* Takes a string, returns the index of the month matching that string, throws
   an error if 0 or more than 1 matches
*/
function parseMonth(month) {
    var matches = monthNames.filter(function(item) { 
        return new RegExp("^" + month, "i").test(item);
    });
    if (matches.length == 0) {
        //throw new Error("Invalid month string");
        throw new Error(label_invalid_month);
    }
    if (matches.length < 1) {
        //throw new Error("Ambiguous month");
        throw new Error(label_ambiguous_month);
    }
    return monthNames.indexOf(matches[0]);
}

/* Same as parseMonth but for days of the week */
function parseWeekday(weekday) {  
    var matches = weekdayNames.filter(function(item) {
        return new RegExp("^" + weekday, "i").test(item);
    });
    if (matches.length == 0) {
        //throw new Error("Invalid day string");
        throw new Error(label_invalid_day);
    }
    if (matches.length < 1) {
        //throw new Error("Ambiguous weekday");
        throw new Error(label_ambiguous_weekday);
    }
    return weekdayNames.indexOf(matches[0]);
}

function DateInRange( yyyy, mm, dd ) {

   // if month out of range
   if ( mm < 0 || mm > 11 )
      //throw new Error('Invalid month value.  Valid months values are 1 to 12');
      throw new Error(label_invalid_month_value);

   if ((typeof(configAutoRollOver) == 'undefined') || (!configAutoRollOver)) {
       // get last day in month
       var d = (11 == mm) ? new Date(yyyy + 1, 0, 0) : new Date(yyyy, mm + 1, 0);
    
       // if date out of range
       if ( dd < 1 || dd > d.getDate() )
          //throw new Error('Invalid date value.  Valid date values for ' + monthNames[mm] + ' are 1 to ' + d.getDate().toString());
          throw new Error(label_invalid_day_of_month_pre + " " + monthNames[mm] + " " + label_invalid_day_of_month_post + " " + d.getDate().toString());
   }

   return true;

}

function getDateObj(yyyy, mm, dd) {
    var obj = new Date();

    obj.setDate(1);
    obj.setYear(yyyy);
    obj.setMonth(mm);
    obj.setDate(dd);
    
    return obj;
}


function parseDateString(s) {  
    for (var i = 0; i < dateParsePatterns.length; i++) {
        var re = dateParsePatterns[i].re;
        var handler = dateParsePatterns[i].handler;
        var bits = re.exec(s);        
        if (bits) {
            //console.log("re = " + re + " -- handler(bits) " + handler(bits) + "\n");
            return handler(bits);
        }
    }
    //throw new Error("Invalid date string");
    throw new Error(label_invalid_date);
}


function magicDateOnlyOnSubmit(id, event) {
    var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
    
    if (keyCode == 13 || keyCode == 10) {
  		magicDate(id);
    }
}
    
function magicDate(user_field_id, ifFormat) {    
    var input = document.getElementById(user_field_id);
    var messagebox = user_field_id + '_msg';
    
    calendarIfFormat = ifFormat;
    // TEST:
    //calendarIfFormat = '%Y-%m-%d';
    
    RemoveClassName(input, 'error');
    document.getElementById(messagebox).innerHTML = '';  
    
    
    try {        
        var parsed_date = parseDateString(input.value);
        //console.log("parsed_date = "+parsed_date);        
        input.value = parsed_date.print(ifFormat); 
        return parsed_date;
    }
    catch (e) {
        AddClassName(input, 'error');
        var message = e.message;
        // Fix for IE6 bug
        if (message.indexOf('is null or not an object') > -1) {
            //message = 'Invalid date string';
            message = label_invalid_date;
        }
        document.getElementById(messagebox).innerHTML = message;
        return false;
    }    
}

// add indexOf function to Array type
// finds the index of the first occurence of item in the array, or -1 if not found
Array.prototype.indexOf = function(item) {
    for (var i = 0; i < this.length; i++) {
        if (this[i] == item) {
            return i;
        }
    }
    return -1;
};

// add filter function to Array type
// returns an array of items judged true by the passed in test function
Array.prototype.filter = function(test) {
    var matches = [];
    for (var i = 0; i < this.length; i++) {
        if (test(this[i])) {
            matches[matches.length] = this[i];
        }
    }
    return matches;
};

// add right function to String type
// returns the rightmost x characters
String.prototype.right = function( intLength ) {
   if (intLength >= this.length)
      return this;
   else
      return this.substr( this.length - intLength, intLength );
};

// add trim function to String type
// trims leading and trailing whitespace
String.prototype.trim = function() { return this.replace(/^\s+|\s+$/, ''); };



// ----------------------------------------------------------------------------
// RemoveClassName
//
// Description : removes a class from the class attribute of a DOM element
//    built with the understanding that there may be multiple classes
//
// Arguments:
//    objElement              - element to manipulate
//    strClass                - class name to remove
//
function RemoveClassName(objElement, strClass)
   {

   // if there is a class
   if ( objElement.className )
      {

      // the classes are just a space separated list, so first get the list
      var arrList = objElement.className.split(' ');

      // get uppercase class for comparison purposes
      var strClassUpper = strClass.toUpperCase();

      // find all instances and remove them
      for ( var i = 0; i < arrList.length; i++ )
         {

         // if class found
         if ( arrList[i].toUpperCase() == strClassUpper )
            {

            // remove array item
            arrList.splice(i, 1);

            // decrement loop counter as we have adjusted the array's contents
            i--;

            }

         }

      // assign modified class name attribute
      objElement.className = arrList.join(' ');

      }
   // if there was no class
   // there is nothing to remove

   }
// 
// RemoveClassName
// ----------------------------------------------------------------------------



// ----------------------------------------------------------------------------
// AddClassName
//
// Description : adds a class to the class attribute of a DOM element
//    built with the understanding that there may be multiple classes
//
// Arguments:
//    objElement              - element to manipulate
//    strClass                - class name to add
//
function AddClassName(objElement, strClass, blnMayAlreadyExist)
   {

   // if there is a class
   if ( objElement.className )
      {

      // the classes are just a space separated list, so first get the list
      var arrList = objElement.className.split(' ');

      // if the new class name may already exist in list
      if ( blnMayAlreadyExist )
         {

         // get uppercase class for comparison purposes
         var strClassUpper = strClass.toUpperCase();

         // find all instances and remove them
         for ( var i = 0; i < arrList.length; i++ )
            {

            // if class found
            if ( arrList[i].toUpperCase() == strClassUpper )
               {

               // remove array item
               arrList.splice(i, 1);

               // decrement loop counter as we have adjusted the array's contents
               i--;

               }

            }

         }

      // add the new class to end of list
      arrList[arrList.length] = strClass;

      // add the new class to beginning of list
      //arrList.splice(0, 0, strClass);
      
      // assign modified class name attribute
      objElement.className = arrList.join(' ');

      }
   // if there was no class
   else
      {

      // assign modified class name attribute      
      objElement.className = strClass;
   
      }

   }
// 
// AddClassName
// ----------------------------------------------------------------------------

  

