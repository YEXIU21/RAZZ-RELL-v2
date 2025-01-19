const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const jwt = require('jsonwebtoken');
require('dotenv').config();

const app = express();
const server = http.createServer(app);

// Health check endpoint for Railway
app.get('/health', (req, res) => {
  res.json({ status: 'healthy' });
});

const io = new Server(server, {
  cors: {
    origin: process.env.CORS_ORIGIN || 'http://localhost:5173',
    methods: ['GET', 'POST'],
    credentials: true,
    allowedHeaders: ['Authorization', 'Content-Type']
  },
  pingTimeout: 60000,
  pingInterval: 25000
});

// Store active users and their socket connections
const activeUsers = new Map();
const userTypingStatus = new Map();

// Middleware to authenticate socket connections
io.use((socket, next) => {
  const token = socket.handshake.auth.token;
  
  if (!token) {
    return next(new Error('Authentication token missing'));
  }

  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    socket.userId = decoded.sub;
    next();
  } catch (err) {
    next(new Error('Invalid token'));
  }
});

io.on('connection', (socket) => {
  console.log(`User connected: ${socket.userId}`);
  activeUsers.set(socket.userId, socket);

  // Handle joining a chat room
  socket.on('join_chat', ({ chatId }) => {
    socket.join(`chat_${chatId}`);
    io.to(`chat_${chatId}`).emit('user_joined', {
      userId: socket.userId,
      timestamp: new Date()
    });
  });

  // Handle leaving a chat room
  socket.on('leave_chat', ({ chatId }) => {
    socket.leave(`chat_${chatId}`);
    io.to(`chat_${chatId}`).emit('user_left', {
      userId: socket.userId,
      timestamp: new Date()
    });
  });

  // Handle new messages
  socket.on('message', async (data) => {
    const { chatId, content, timestamp } = data;
    
    // Emit the message to all users in the chat room
    io.to(`chat_${chatId}`).emit('new_message', {
      chatId,
      message: {
        senderId: socket.userId,
        content,
        timestamp,
        read: false
      }
    });
  });

  // Handle typing status
  socket.on('typing', ({ chatId, isTyping }) => {
    const roomKey = `chat_${chatId}`;
    let typingUsers = userTypingStatus.get(roomKey) || new Set();
    
    if (isTyping) {
      typingUsers.add(socket.userId);
    } else {
      typingUsers.delete(socket.userId);
    }
    
    userTypingStatus.set(roomKey, typingUsers);
    
    socket.to(roomKey).emit('typing_status', {
      chatId,
      typingUsers: Array.from(typingUsers)
    });
  });

  // Handle disconnection
  socket.on('disconnect', () => {
    console.log(`User disconnected: ${socket.userId}`);
    activeUsers.delete(socket.userId);
    
    // Clean up typing status
    for (const [roomKey, typingUsers] of userTypingStatus.entries()) {
      if (typingUsers.has(socket.userId)) {
        typingUsers.delete(socket.userId);
        if (typingUsers.size === 0) {
          userTypingStatus.delete(roomKey);
        } else {
          io.to(roomKey).emit('typing_status', {
            chatId: roomKey.replace('chat_', ''),
            typingUsers: Array.from(typingUsers)
          });
        }
      }
    }
  });
});

const PORT = process.env.PORT || 3001;
const HOST = '0.0.0.0';

server.listen(PORT, HOST, () => {
  console.log(`Socket server running on port ${PORT}`);
}); 