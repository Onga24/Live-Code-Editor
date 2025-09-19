// const express = require('express');
// const http = require('http');
// const { Server } = require('socket.io');
// const cors = require('cors');

// const app = express();
// app.use(cors());
// const server = http.createServer(app);

// const io = new Server(server, {
//     cors: {
//         origin: '*', // for dev, later replace with your frontend URL
//         methods: ['GET', 'POST']
//     }
// });

// io.on('connection', (socket) => {
//     console.log('a user connected:', socket.id);

//     // Join project room
//     socket.on('joinProject', (projectId) => {
//         socket.join(`project_${projectId}`);
//         console.log(`Socket ${socket.id} joined project_${projectId}`);
//     });

//     // Listen for code changes
//     socket.on('codeChange', ({ projectId, code }) => {
//         // Broadcast to other clients in the room
//         socket.to(`project_${projectId}`).emit('receiveCode', code);
//     });

//     socket.on('disconnect', () => {
//         console.log('user disconnected:', socket.id);
//     });
// });

// const PORT = 3001;
// server.listen(PORT, () => console.log(`Socket.IO server running on port ${PORT}`));



const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');

const app = express();
app.use(cors());
const server = http.createServer(app);

const io = new Server(server, {
    cors: {
        origin: '*',
        methods: ['GET', 'POST']
    }
});

io.on('connection', (socket) => {
    console.log('a user connected:', socket.id);

    socket.on('joinProject', (projectId) => {
        socket.join(`project_${projectId}`);
        console.log(`Socket ${socket.id} joined project_${projectId}`);
    });

    socket.on('codeChange', ({ projectId, code }) => {
        socket.to(`project_${projectId}`).emit('receiveCode', code);
    });

    socket.on('codeOutput', ({ projectId, output }) => {
        socket.to(`project_${projectId}`).emit('receiveOutput', output);
        console.log(`Execution result for project_${projectId}:`, output);
    });

    socket.on('disconnect', () => {
        console.log('user disconnected:', socket.id);
    });
});

const PORT = 3001;
server.listen(PORT, () => console.log(`Socket.IO server running on port ${PORT}`));

