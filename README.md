# Jewellery CRM Pro

Jewellery CRM Pro is a full-stack Laravel + React CRM made for jewellery showrooms, exhibition teams, sales staff, and business owners who want one place to manage leads, customers, sales, custom orders, events, campaigns, follow-ups, loyalty, gift cards, staff work, and reports.

The main idea of this software is simple: every visitor, inquiry, exhibition lead, purchase, follow-up, WhatsApp message, offer, task, and repeat visit becomes reusable business data. The CRM is not only for storing records; it is designed to help owners understand what is working, which marketing source is bringing serious customers, which staff member is converting, which customers should be called again, and which leads can still be recovered.

## Project summary

| Area | Detail |
|---|---|
| Project name | Jewellery CRM Pro |
| Backend | Laravel 12, PHP 8.2+, Sanctum, REST API |
| Frontend | React, Vite, Tailwind CSS, Axios, React Router |
| Database | MySQL, with SQLite support for quick demo/testing |
| Authentication | Laravel Sanctum token authentication |
| UI style | Premium jewellery showroom style: cream, white, soft gold, charcoal, elegant cards |
| Main users | Owner, Super Admin, Management, Sales Manager, Sales Executive, Front Office CRE, Event Manager, Accountant |

## Software purpose from start to end

The complete business flow is:

1. A person comes from walk-in, Instagram, Facebook, WhatsApp, website, referral, phone call, exhibition, or event.
2. Staff creates a lead or captures it through the public exhibition QR form.
3. The system checks duplicate mobile/email and highlights returning people.
4. The lead is assigned to staff with source, priority, interest, budget, occasion, and expected purchase date.
5. Staff adds notes, follow-ups, communication logs, files, and next action.
6. If the person returns in another exhibition/event, a diamond badge appears beside the name.
7. Clicking the diamond badge opens that person's journey: old exhibitions, purchases, custom orders, follow-ups, campaigns, communications, loyalty, gift cards, and transaction value.
8. When the lead purchases or becomes serious, it is converted to a customer.
9. Customer profile becomes the permanent 360-degree record: personal details, family dates, purchases, interests, custom orders, loyalty wallet, gift cards, notes, and category.
10. Sales, payments, invoices, items, discounts, taxes, and staff commission are recorded.
11. Custom jewellery orders are tracked from design to delivery with status timeline and reminders.
12. Offers, WhatsApp campaigns, email campaigns, loyalty points, gift cards, and festival campaigns are created using existing CRM data.
13. Retention and smart task engines generate practical daily work for staff.
14. Reports show conversion, sales, staff performance, event ROI, lost leads, loyalty, gift cards, custom orders, and follow-up quality.
15. Owner uses dashboard and reports to see what marketing is doing, where money is converting, and how to increase repeat visits.

## Niche presentation points for business owners

- The CRM shows not only how many leads came, but which source created valuable customers.
- Exhibition visitors are not lost after the event; they are added to a long-term customer journey.
- Returning visitors are instantly identified with a diamond badge, helping staff greet them with context and trust.
- The owner can compare exhibitions by leads, conversion, sales, expense, revenue, profit, and ROI.
- Staff can see today’s smart work instead of manually guessing whom to call.
- Marketing can reuse customer data for WhatsApp, email, offers, birthday wishes, anniversary wishes, winback campaigns, and festival campaigns.
- Customer profiles show purchase taste, budget range, jewellery interest, occasion, and expected buying time.
- Loyalty and gift cards help bring customers back instead of only chasing new leads.
- Lost leads and inactive customers can be recovered through structured follow-up and winback messages.
- Reports help owners understand showroom performance, staff performance, campaign performance, and customer repeat behaviour.

## Main modules

### 1. Authentication, roles, and permissions

The system includes admin/staff login using Laravel Sanctum. Seeded roles are:

- Super Admin
- Sales Manager
- Sales Executive
- Event Manager
- Accountant
- Management
- Front Office CRE

The backend contains roles, permissions, gates/policies, staff management, and user activity logs. This allows the owner to control who can view, create, update, or delete important CRM data.

### 2. Dashboard

The dashboard gives the owner a live business summary:

- Total leads
- Today follow-ups
- Pending and overdue follow-ups
- Converted leads
- Lost leads
- Total sales
- Custom orders pending
- Exhibition leads
- Gift cards issued
- Loyalty points summary
- Sales performance
- Lead source-wise performance
- Staff-wise performance
- Marketing and retention intelligence

The dashboard is built from real database counts, not static demo numbers.

### 3. Lead management

Leads support:

- Add, edit, delete, list, and detail page
- Sources: Walk-in, Instagram, Facebook, WhatsApp, Website, Referral, Exhibition, Event, Phone Call
- Status: New, Contacted, Interested, Follow-up, Converted, Lost
- Priority: Hot, Warm, Cold
- Staff assignment
- Notes
- Timeline
- Attachments metadata
- Tags
- Lead history
- Duplicate detection by mobile/email
- CSV import and export
- Conversion to customer

Lead management is designed so sales staff always know what happened last and what should happen next.

### 4. Follow-up management

Follow-ups can be created for leads and customers. Supported types:

- Call
- WhatsApp
- Visit
- Meeting
- Email

The system tracks follow-up date/time, reminder status, notes, next follow-up date, overdue list, today list, staff-wise list, and timeline on profiles.

### 5. Customer management

Customers are the long-term asset of the jewellery business. Customer profiles include:

- Personal details
- Mobile/email/address
- Birthday and anniversary
- Family members
- Customer category: Normal, Premium, VIP, HNI
- Product interests
- Budget range
- Occasion
- Expected purchase date
- Purchase history
- Custom order history
- Loyalty wallet
- Gift cards
- Notes
- Communication history
- Retention profile

This gives the showroom a complete customer memory.

### 6. Sales management

Sales include:

- Customer selection
- Product category
- Jewellery type
- Metal type
- Gold purity
- Diamond details
- Gross weight
- Net weight
- Stone weight
- Making charge
- Wastage
- Discount
- Tax
- Final amount
- Payment status: Paid, Partial, Pending
- Payment mode: Cash, UPI, Card, Bank Transfer, Cheque
- Invoice number generation
- Staff commission tracking
- Loyalty point earning

Sales records are connected with customers, staff, payments, loyalty, and reporting.

### 7. Custom order management

Custom jewellery orders include:

- Customer details
- Design reference image/path
- Jewellery type
- Metal type
- Carat/purity
- Approx weight
- Estimated amount
- Advance payment
- Due date
- Order status
- Karigar/vendor assignment
- Internal notes
- Customer approval status
- Order timeline
- Delivery reminder

Order statuses:

- Processing
- Order Ready
- Cancelled

Each status update is stored in the custom order timeline.

### 8. Exhibition and event CRM

The exhibition module is built for professional event handling:

- Create exhibitions/events
- Event name and location
- Start/end date
- Stall number
- Staff assigned
- Event expense
- Leads collected
- Event-wise lead list
- Event follow-ups
- Event sales conversion
- Event ROI report
- QR-based public lead capture form
- Export event leads
- Returning visitor detection
- Diamond badge on returning leads/customers
- Customer journey modal from diamond badge

This helps the owner understand whether an exhibition actually produced business or only collected contacts.

### 9. Marketing command center

Marketing is one of the most important parts of this CRM. The system supports:

- WhatsApp campaigns
- Email campaigns
- Offer campaigns
- Audience preview
- Customer segment selection
- Campaign recipient logs
- Engagement tracking
- Conversion attribution
- Offer/event linking
- Personalized placeholders

Useful campaign ideas:

- Bridal jewellery inquiries
- Diamond jewellery interest
- Old exhibition visitors
- VIP customers
- HNI customers
- Birthday and anniversary wishes
- Festival campaigns
- Lost lead winback
- Inactive customer revisit campaign
- Gift card balance reminders
- Loyalty point expiry reminders

The goal is to reuse already collected data to increase revisits and customer trust.

### 10. Offer and campaign management

Offers include:

- Discount
- Making charge off
- Gift
- Cashback
- Loyalty bonus

Each offer can have start/end date, customer type, product category, coupon code, usage limit, status, assigned customers, and usage tracking.

### 11. Loyalty program

Loyalty includes:

- Rule setup
- Points based on purchase amount
- Earn points on sale
- Redeem points in sale
- Customer wallet
- Points history
- Expiry date
- Manual adjustment
- VIP upgrade logic

This is important for repeat visits and long-term customer relationships.

### 12. Gift card management

Gift cards include:

- Code generation
- Gift card amount
- Issued customer
- Expiry date
- Redemption
- Partial redemption
- Balance tracking
- Status: Active, Used, Expired, Cancelled

### 13. Product interest management

The CRM tracks customer interest in:

- Bridal jewellery
- Gold jewellery
- Diamond jewellery
- Silver jewellery
- Polki
- Kundan
- Custom design
- Budget range
- Occasion
- Expected purchase date

This data is later reused for targeted marketing and smart follow-ups.

### 14. Task and smart work module

Manual tasks support:

- Assign task to staff
- Due date
- Priority
- Status
- Related lead/customer/order/event
- Notes
- Overdue alert

The Smart Task Recommendation Engine adds automatic business intelligence:

- Finds hot leads needing contact
- Finds overdue follow-ups
- Finds pending payments
- Finds custom order delays
- Finds inactive customers
- Finds birthday/anniversary/festival opportunities
- Assigns work to the right staff
- Prevents duplicate task generation
- Gives reason, priority, next action, call script, WhatsApp message, and suggested offer

This helps staff start the day with a clear work plan.

### 15. Communication logs

The CRM stores:

- WhatsApp logs
- Call logs
- Email logs
- SMS logs
- Manual notes
- Communication timeline

This helps staff avoid repeating the same conversation and gives owners transparency.

### 16. Reports and analytics

Reports include:

- Lead conversion report
- Staff performance report
- Sales report
- Event ROI report
- Follow-up report
- Lost lead report
- Customer purchase report
- Loyalty report
- Gift card report
- Custom order report

Reports support date filtering and are prepared for export workflows.

### 17. Settings

Settings include:

- Company profile
- Branches
- Staff management
- Lead sources
- Jewellery categories
- Metal types
- Payment modes
- Tax settings
- Loyalty rules
- Offer settings
- Invoice settings

## Marketing-first CRM strategy

This CRM is designed around one big business truth: jewellery customers often do not buy immediately. They compare, wait for family decisions, come again during festivals, revisit at exhibitions, ask for custom designs, and respond better when the showroom remembers them.

Jewellery CRM Pro helps the business reuse data in these ways:

1. Capture every inquiry properly.
2. Tag interest and budget from the first conversation.
3. Identify repeat visitors across events.
4. Show staff complete customer context before speaking.
5. Create audience segments from real behaviour.
6. Send personalized WhatsApp/email messages.
7. Use birthdays, anniversaries, festivals, gift cards, and loyalty as revisit reasons.
8. Recover old leads through winback campaigns.
9. Compare campaign results with actual sales.
10. Build customer trust because the showroom remembers their preferences and history.

## Important customer journey examples

### Example 1: Exhibition visitor becomes customer

1. Visitor scans QR code at exhibition.
2. Lead is created with event source.
3. Staff adds interest: diamond jewellery, budget range, wedding occasion.
4. Follow-up is scheduled after the event.
5. Visitor comes to showroom.
6. Lead is converted to customer.
7. Sale is created.
8. Loyalty points are earned.
9. Customer is added to future bridal/diamond campaigns.

### Example 2: Returning exhibition visitor

1. A person came to Exhibition A last month.
2. Same mobile/email appears in Exhibition B.
3. CRM detects the return.
4. Diamond badge appears beside the name.
5. Staff clicks the badge.
6. Staff sees previous visit, notes, interest, transactions, follow-ups, and communication.
7. Staff can speak personally: “Last time you liked diamond bangles; we have a new collection.”

### Example 3: Retention and revisit

1. Customer purchased jewellery six months ago.
2. CRM detects cleaning/reminder opportunity.
3. Smart task is created.
4. Staff sends personalized WhatsApp.
5. Customer revisits showroom.
6. New offer or product interest is recorded.
7. Future marketing becomes more accurate.

## Backend folder structure

```text
app/
  Http/Controllers/Api/     REST API controllers
  Http/Requests/            Form Request validation
  Http/Resources/           API response resources
  Http/Middleware/          Permission middleware
  Models/                   Eloquent models and relationships
  Services/                 Business logic services
database/
  factories/                Demo data factories
  migrations/               Complete database schema
  seeders/                  Demo users, roles, CRM data
routes/
  api.php                   API routes
  web.php                   Web route / Laravel entry
tests/
  Feature/                  API feature tests
```

## Frontend folder structure

```text
frontend/
  src/
    components/             Shared layout and UI components
    pages/                  CRM pages
    api.js                  Axios API client
    AuthContext.jsx         Login/session state
    App.jsx                 React routes
  package.json              Frontend dependencies and scripts
```

## Database tables

Major tables include:

- users
- roles
- permissions
- leads
- lead_notes
- lead_followups
- customers
- customer_family_members
- sales
- sale_items
- payments
- custom_orders
- custom_order_status_logs
- exhibitions
- exhibition_leads
- offers
- offer_usages
- loyalty_points
- gift_cards
- gift_card_transactions
- tasks
- communication_logs
- activity_logs
- settings
- branches
- marketing_campaigns
- campaign_recipients
- message_templates
- customer_important_dates
- retention_messages
- customer_retention_scores
- task_rules
- task_outcomes

## API overview

Authentication:

```text
POST /api/auth/login
GET  /api/auth/me
POST /api/auth/logout
```

Core resources:

```text
/api/leads
/api/followups
/api/customers
/api/sales
/api/custom-orders
/api/exhibitions
/api/offers
/api/gift-cards
/api/tasks
/api/communications
/api/staff
/api/marketing-campaigns
```

Special workflows:

```text
POST /api/leads/import
GET  /api/leads/export
POST /api/leads/{lead}/convert
POST /api/leads/{lead}/notes
GET  /api/leads/{lead}/journey
POST /api/custom-orders/{order}/status
GET  /api/exhibitions/{event}/roi
POST /api/events/{publicToken}/capture
GET  /api/marketing/dashboard
POST /api/marketing/preview
POST /api/marketing-campaigns/{campaign}/launch
PATCH /api/campaign-recipients/{recipient}/engagement
GET  /api/loyalty/{customer}
POST /api/loyalty/{customer}/adjust
POST /api/gift-cards/{card}/redeem
GET  /api/reports/{reportType}
GET|PUT /api/settings
```

To see the complete generated list:

```bash
php artisan route:list --path=api
```

## Setup commands

### Backend setup

Requirements:

- PHP 8.2+
- Composer 2+
- MySQL 8+
- XAMPP or similar local PHP/MySQL stack

Commands:

```bash
composer install
copy .env.example .env
php artisan key:generate
```

Create MySQL database:

```sql
CREATE DATABASE jewellery_crm_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Update `.env` database values:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jewellery_crm_pro
DB_USERNAME=root
DB_PASSWORD=
```

Run migration and seeder:

```bash
php artisan migrate
php artisan db:seed
```

For a fresh local demo:

```bash
php artisan migrate:fresh --seed
```

Start Laravel API:

```bash
php artisan serve
```

API URL:

```text
http://127.0.0.1:8000/api
```

### Frontend setup

```bash
cd frontend
npm install
npm run dev
```

Frontend URL:

```text
http://localhost:5173
```

### Production frontend build

```bash
cd frontend
npm run build
```

The static frontend build is generated inside `frontend/dist`.

## Demo login credentials

All seeded accounts use this password:

```text
Password@123
```

| Role | Email |
|---|---|
| Super Admin | admin@jewellerycrm.test |
| Sales Manager | manager@jewellerycrm.test |
| Sales Executive | sales@jewellerycrm.test |
| Event Manager | events@jewellerycrm.test |
| Accountant | accounts@jewellerycrm.test |

## Daily owner workflow

1. Open dashboard.
2. Check total new leads, pending follow-ups, overdue follow-ups, sales, and custom orders.
3. Open My Smart Work / Smart Tasks.
4. Review high-priority leads and customers.
5. Check event/exhibition performance.
6. Launch or review marketing campaigns.
7. Check reports at day end.
8. Use lost lead and winback reports for recovery campaigns.

## Daily sales staff workflow

1. Login.
2. Open My Smart Work.
3. Call hot leads first.
4. Update follow-up status after each call/WhatsApp.
5. Add communication notes.
6. Convert serious leads to customers.
7. Create sale or custom order if customer purchases.
8. Schedule next follow-up where needed.

## Daily event staff workflow

1. Create exhibition/event.
2. Share QR public capture form.
3. Capture leads at stall.
4. Add interest, budget, and product type.
5. Check diamond badge for repeat visitors.
6. Assign leads to sales staff.
7. After event, review event ROI and pending follow-ups.

## Verification commands

```bash
php artisan test
```

```bash
cd frontend
npm run lint
npm run build
```

Run intelligence engines manually:

```bash
php artisan retention:scan
php artisan smart-tasks:generate
```

Production scheduler:

```bash
php artisan schedule:work
```

Or configure the normal Laravel scheduler cron to run every minute.

## What has been completed

- Laravel REST API backend
- React Vite frontend
- Sanctum authentication
- Role and permission structure
- Dashboard with database metrics
- Lead/customer/sales/custom order/event modules
- Exhibition QR lead capture
- Returning visitor diamond badge concept and customer journey flow
- Marketing campaigns for WhatsApp/email workflows
- Offer, loyalty, and gift card modules
- Retention engine
- Smart task recommendation engine
- Reports and settings modules
- Demo seed data and demo logins
- README setup and business flow documentation

## Recommended next improvements

- Connect real WhatsApp Business API provider.
- Connect real email provider such as SMTP, SES, Mailgun, or SendGrid.
- Add PDF invoice template with showroom branding.
- Add Excel/PDF exports for all reports.
- Add role-wise UI hiding for restricted staff.
- Add production queue workers for campaign sending.
- Add SMS provider if required.
- Add cloud storage for design images and attachments.
