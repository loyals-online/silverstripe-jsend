var JSend = function (status, data, message, code) {
    this.STATUS_FAIL = 'fail';
    this.STATUS_ERROR = 'error';
    this.STATUS_SUCCESS = 'success';

    // private instance variables
    this.data = data;
    this.status = status;
    this.message = message;
    this.code = code;
};

JSend.parse = function (result) {

    if (typeof result.status == 'undefined') {
        result = JSON.parse(result);
        if (typeof result.status == 'undefined') {
            throw 'Could not parse data; required element \'status\' missing.';
        }
    }

    var response = new JSend(result.status);

    if (typeof result.code == 'undefined') {
        result.code = 200;
    }
    response.setCode(result.code);

    if (result.status == response.STATUS_ERROR) {
        if (typeof result.message == 'undefined') {
            throw 'Could not parse data; required element \'message\' missing.';
        }
        response.setMessage(result.message);
    }

    if (result.status == response.STATUS_SUCCESS || result.status == response.STATUS_FAIL) {
        if (typeof result.data == 'undefined') {
            throw 'Could not parse data; required element \'data\' missing.';
        }
        response.setData(result.data);
    } else if (result.status == response.STATUS_ERROR) {
        if (typeof result.data != 'undefined') {
            response.setData(result.data);
        }
    }

    return response;
};

JSend.prototype = {
    getStatus: function () {
        return this.status;
    },
    setStatus: function (status) {

        if (!jQuery.inArray(status, [this.STATUS_FAIL, this.STATUS_ERROR, this.STATUS_SUCCESS])) {
            throw 'An invalid JSend status string was given.';
        }

        this.status = status;

        return this;
    },
    getCode: function () {
        return this.code;
    },
    setCode: function (code) {
        if (!(new RegExp('^[0-9]+$')).test(code)) {
            throw 'An invalid JSend status code was given.';
        }

        this.code = code;

        return this;
    },
    isSuccess: function () {
        return this.status == this.STATUS_SUCCESS;
    },
    isFail: function () {
        return this.status == this.STATUS_FAIL;
    },
    isError: function () {
        return this.status == this.STATUS_ERROR;
    },
    hasData: function () {
        return this.data !== null;
    },
    getData: function () {
        return this.data;
    },
    setData: function (data) {
        this.data = data;

        return this;
    },
    hasMessage: function () {
        return this.message !== null;
    },
    getMessage: function () {
        return this.message;
    },
    setMessage: function (message) {
        this.message = message;
        return this;
    }
};

