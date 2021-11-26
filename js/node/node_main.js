var io = require('socket.io').listen(8001);
var fs = require('fs');
io.set('log level', 1);

io.sockets.on('connection', function(socket) {
    var dir = './function/';
    fs.readdir(dir, function(err, files) {
        if (err)
            throw err;
        files.forEach(function(file) {
            var name = dir + file;
            if (fs.statSync(name).isDirectory()) {

            } else {
                eval(fs.readFileSync(name) + '');
            }
        });
    });
});  