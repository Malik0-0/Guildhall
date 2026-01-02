<div align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="200" alt="Laravel Logo">
  
  <h1>🏰 Guildhall</h1>
  
  <p>
    <strong>The Gamified Service Marketplace Platform</strong>
  </p>
  
  <p>
    Connecting skilled service providers (Adventurers) with clients (Patrons) through an engaging quest-based system
  </p>
  
  <p>
    <a href="#-key-features">Features</a> •
    <a href="#-business-model">Business Model</a> •
    <a href="#-technology-stack">Technology</a> •
    <a href="#-demo">Demo</a> •
    <a href="#-getting-started">Getting Started</a>
  </p>
</div>

---

## 🎯 Executive Summary

**Guildhall** is a revolutionary service marketplace platform that transforms traditional freelance work into an engaging gamified experience. By incorporating RPG elements like gold currency, experience points, leveling systems, and trust tiers, we've created a unique ecosystem that motivates service providers and builds trust with clients.

### 📊 Key Metrics
- **Development Status**: Core Backend Complete ✅
- **Production Ready**: Backend MVP Ready 🚀
- **Target Market**: Service marketplace & freelance economy
- **Competitive Edge**: Gamification + Trust System

---

## 🌟 Key Features

### 💰 **Gold Currency System**
- Virtual economy with real monetary value
- Seamless payment processing via Midtrans
- Secure wallet management for all users
- Multiple payment methods supported

### 📈 **XP & Leveling System**
- Experience points earned through completed quests
- Progressive leveling with visual achievements
- Skill development tracking
- Performance-based rewards

### 🛡️ **Trust Tiers**
- 5-tier trust system: Bronze → Silver → Gold → Platinum → Diamond
- Based on completed quests and user ratings
- Visual trust indicators for better decision-making
- Reduced risk for patrons

### 🔍 **Smart Matching**
- Category-based quest organization
- Tag system for skill matching
- Advanced search and filtering
- Proposal system for complex projects

### 💬 **Real-time Communication**
- Built-in messaging system
- File sharing capabilities
- Quest-specific conversations
- Real-time notifications

### ⭐ **Reviews & Ratings**
- Two-way rating system
- Detailed feedback mechanisms
- Public reputation building
- Quality assurance

---

## 💼 Business Model

### Revenue Streams
1. **Commission Fees**: 10-15% on completed quests
2. **Premium Features**: Advanced matching, priority placement
3. **Gold Package Sales**: Virtual currency purchases
4. **Subscription Tiers**: Enhanced features for power users

### Target Audiences
- **Patrons**: Businesses and individuals seeking services
- **Adventurers**: Skilled professionals offering services
- **Enterprise**: Teams managing multiple service providers

### Market Opportunity
- Global freelance market: $4.5T+ (2023)
- Growing gig economy adoption
- Increasing demand for trusted service providers
- Untapped gamification potential in service marketplaces

---

## 🛠 Technology Stack

### Backend
- **Framework**: Laravel 12.0 (PHP 8.2+)
- **Database**: SQLite/MySQL/PostgreSQL (multi-database support)
- **Queue System**: Redis + Laravel Queues
- **Real-time**: Laravel Reverb (WebSockets)

### Frontend
- **Styling**: Tailwind CSS 4.0
- **Build Tool**: Vite 7.0
- **JavaScript**: Modern ES6+ with Axios
- **Components**: Custom Blade components

### Infrastructure
- **Payment**: Midtrans Integration
- **File Storage**: Local/Cloud support
- **Caching**: Redis/Database
- **Email**: Queue-based notifications

### Development Tools
- **Testing**: PHPUnit 11.5
- **Code Quality**: Laravel Pint
- **Development**: Laravel Sail (Docker)
- **Monitoring**: Laravel Pail

---

## 🎮 Demo & User Journey

### For Patrons (Clients)
1. **Post a Quest**: Describe your service needs
2. **Set Budget**: Assign gold value to the quest
3. **Review Proposals**: Evaluate adventurer applications
4. **Track Progress**: Monitor quest completion
5. **Approve & Pay**: Release gold upon satisfaction

### For Adventurers (Service Providers)
1. **Build Profile**: Showcase skills and experience
2. **Browse Quests**: Find matching opportunities
3. **Submit Proposals**: Apply for interesting quests
4. **Complete Work**: Deliver quality service
5. **Earn Rewards**: Receive gold + XP + reviews

### Gamification Elements
- **Level Progression**: Visual advancement system
- **Trust Badges**: Display credibility tiers
- **Achievement Unlocks**: New features at higher levels
- **Leaderboards**: Top performer rankings

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- Database (SQLite/MySQL/PostgreSQL)

### Quick Installation

```bash
# Clone the repository
git clone https://github.com/your-username/guildhall.git
cd guildhall

# Run automated setup
composer run setup

# Start development environment
composer run dev
```

### Manual Setup

```bash
# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate

# Build assets
npm run build

# Start server
php artisan serve
```

### Environment Configuration

Key environment variables to configure:

```env
# Application
APP_NAME="Guildhall"
APP_URL=http://localhost

# Database
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_DATABASE=guildhall

# Midtrans Payments
MIDTRANS_MERCHANT_ID=your_merchant_id
MIDTRANS_SERVER_KEY=your_server_key
MIDTRANS_CLIENT_KEY=your_client_key
MIDTRANS_IS_PRODUCTION=false
```

---

## 📱 System Architecture

### Core Models
- **Users**: Dual-role system (Patrons/Adventurers)
- **Quests**: Service requests with gamified elements
- **Profiles**: Skill management and statistics
- **Reviews**: Two-way reputation system
- **Messages**: Real-time communication

### Key Workflows
1. **Quest Creation**: Patron posts service request
2. **Matching System**: Algorithm suggests suitable adventurers
3. **Proposal Phase**: Adventurers apply with proposals
4. **Execution Phase**: Work completion and evidence
5. **Completion**: Approval and reward distribution

---

## 🔒 Security & Trust

### Security Measures
- Laravel's built-in authentication
- Secure payment processing via Midtrans
- File upload validation and scanning
- SQL injection protection
- XSS prevention

### Trust Building
- Verified profiles
- Review system
- Completion rate tracking
- Dispute resolution mechanism
- Escrow-style payment protection

---

## 📈 Scalability & Performance

### Optimizations
- Database query optimization
- Caching strategies implemented
- Queue-based background processing
- Asset optimization with Vite
- Lazy loading for large datasets

### Scaling Considerations
- Horizontal scaling support
- Database read/write splitting
- CDN integration ready
- Load balancer compatible
- Microservice architecture ready

---

## 🎯 Roadmap

### Phase 1: MVP (Current)
- ✅ Core quest system (CRUD operations)
- ✅ User management & authentication
- ✅ Payment integration (Midtrans)
- ✅ Review & rating system
- ✅ Messaging system
- ✅ Proposal system
- ✅ Gamification (gold, XP, levels)
- ✅ Skills management
- ✅ Withdrawal system

### Phase 2: Enhanced Features
- 🔄 Mobile app development
- 🔄 Advanced analytics dashboard
- 🔄 API for third-party integrations
- 🔄 Subscription tiers

### Phase 3: Enterprise
- 📋 Team management features
- 📋 White-label solutions
- 📋 Advanced reporting
- 📋 SLA management

---

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Commands
```bash
# Run tests
composer run test

# Code formatting
./vendor/bin/pint

# Start all services
composer run dev
```

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 📞 Contact

- **Project Lead**: Malik Alrasyid Basori
- **Email**: malikalrasyidbasori.1@gmail.com

---

## 🙏 Acknowledgments

- Laravel Framework for the robust foundation
- Midtrans for seamless payment integration
- Tailwind CSS for beautiful UI components
- Our amazing beta testers and early adopters

---

<div align="center">
  <p>
    <strong>🏰 Guildhall - Where Every Service is an Adventure</strong>
  </p>
  <p>
    Built with ❤️ using Laravel 12.0
  </p>
</div>
