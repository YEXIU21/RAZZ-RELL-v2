# Razz Rell Events - Professional Event Management System

A modern, full-stack event management system built with Laravel and Vue.js, designed to streamline event planning and management.

## 🌟 Features

- **User Management**
  - Secure authentication and authorization
  - User roles (Admin, Event Planner, Client)
  - Profile management with avatar support

- **Event Management**
  - Comprehensive booking system
  - Real-time availability checking
  - Package customization
  - Event portfolio showcase

- **Communication**
  - Real-time chat system
  - Message notifications
  - Contact form integration

- **Payment & Billing**
  - Secure payment processing
  - Invoice generation
  - Payment tracking

- **Analytics & Reporting**
  - Event statistics
  - Revenue tracking
  - Customer satisfaction metrics

## 🚀 Tech Stack

### Frontend
- Vue.js 3
- Vite
- Tailwind CSS
- Socket.io Client
- Chart.js for analytics
- Vue Router
- Pinia for state management

### Backend
- Laravel 9
- MySQL
- Node.js for WebSocket server
- JWT Authentication
- RESTful API

## 📦 Installation

1. **Setup the project**
   ```bash
   cd RAZZ-RELL
   ```

2. **Backend Setup**
   ```bash
   cd backend
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   php artisan db:seed
   ```

3. **Frontend Setup**
   ```bash
   cd frontend
   npm install
   cp .env.example .env
   ```

4. **Start Development Servers**
   ```bash
   # Backend
   cd backend
   php artisan serve

   # Socket Server
   node socket-server.js

   # Frontend
   cd frontend
   npm run dev
   ```

## 🔧 Environment Setup

### Backend (.env)
```env
APP_NAME=RazzrelEvents
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Frontend (.env)
```env
VITE_API_URL=http://localhost:8000
VITE_SOCKET_URL=http://localhost:3001
VITE_GOOGLE_MAPS_API_KEY=your_google_maps_key
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch
3. Make your changes
4. Submit your changes
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Authors

- **YEXIU21** - *Initial work*

## 🙏 Acknowledgments

- Thanks to all contributors who have helped shape Razz Rell Events
- Special thanks to the Laravel and Vue.js communities for their excellent documentation
- Icons provided by Heroicons and Font Awesome 