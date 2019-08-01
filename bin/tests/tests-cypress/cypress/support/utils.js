/**
 * Get input from form data query
 *
 * @param identifier
 * @param data
 * @returns {string}
 */
exports.fetchInput = function (identifier, data) {
    var form_data = data.split('&');
    var valueInput = "";

    Cypress.$.each(form_data, function (key, value) {
        var data = value.split('=');
        if (identifier == data[0]) {
            valueInput = decodeURIComponent(data[1]);
        }
    });
    return valueInput;
};

function jsonParseDeep(json) {
    try {
        let obj = json;

        if (typeof obj !== 'object') {
            obj = JSON.parse(json);
            if (typeof obj !== 'object') {
                obj = String(obj);
            }
        } else if (obj === null){
            obj = "";
        }

        if (typeof obj === 'object') {
            for (let prop in obj) {
                if (obj.hasOwnProperty(prop)) {
                    obj[prop] = jsonParseDeep(obj[prop]);
                }
            }
        }

        return obj;
    } catch (e) {
        return String(json);
    }
}

exports.jsonParseDeep = jsonParseDeep;