/**
 *  parsing a JSON file to its list of keys
 *  @param
 *      json - the file
 *      prefix - first word of all keys
 *      keyList - string I/O
 *  @returns string keyList
 */
function parseJSON2TicketKeys(json, prefix, keyList) {
    for (key in json) {
        if (typeof (json[key]) === "object") {
            keyList = parseJSON2Keys(json[key], prefix + key + '.', keyList);
        } else {
            keyList += '<p class="' + prefix + key + '">' + '{{' + prefix + key + '}}' + '</p>' + '\r\n';
        }
        ;
    }
    ;
    return keyList;
}
;

/**
 * get one ticket and parse it into a html template
 * !!! NOT FOR PROD !!!
 * @returns {null}
 */
function getOneTicketSampleForTemplate() {
    var request = new XMLHttpRequest();

    request.open('GET', 'http://127.0.0.1/tck.php/ticket/14586/print?direct&json', true);

    request.onload = function () {
        var tickets = JSON.parse(this.response);
        var key;
        var ticket = tickets[0];
        var keyList = '<div class="page"><div class="ticket"><div class="logo">{{logo}}</div>';


        var keySide = parseJSON2Keys(ticket, '', '');
        keyList += '<div class="left">' + keySide + '</div>';
        keyList += '<div class="right">' + keySide + '</div>';
        keyList += '</div>';

        document.getElementById("myTicket").innerHTML = keyList;
    };

    request.send();
}

/**
 *  change all values of an object
 *  @param object // in this case a ticket
 */
function changeMyValues(object){
     function getAllKeys(o) {
        Object.keys(o).forEach(function (k) {
            console.log(k);
            if (typeof o[k] === 'object'&& o[k]!==null) {
                return getAllKeys(o[k]);
            }
            allKeys[k] = false;
        });
    }

    var allKeys = Object.create(null);

    getAllKeys(object);
    return allKeys;
}

/**
 *  parsing a JSON file to its list of keys
 *  @param json - the file
 *  @param     prefix - first word of all keys
 * 
 *  @returns array keyList
 */
function parseJSON2Keys(json, prefix) {
    var keyList=[];
    for (key in json) {
        if (typeof (json[key]) === "object" && json[key]!==null) {
            keyList = keyList.concat(parseJSON2Keys(json[key], prefix + key + '.'));
        } else {
            keyList.push(prefix + key);
            console.log(prefix+key);
        }
        ;
    }
    return keyList;
}

/**
 * creating param JSON file for the custom printing
 * @param {object} ticketJson object.ticket from any json file
 * @returns {string} a string in JSON that can be save and reuse in customization of the tickets
 */
function createTicketParam(ticketJson){
    var keyList = parseJSON2Keys(ticketJson,'');
    var myTicketParam = {};
    for(var key in keyList){
        myTicketParam[keyList[key]]={'optional':true, 'displayed':true};
    };
    return JSON.stringify(myTicketParam, null, 2);
}

