# Kalasha Fine Jewels CRM — Complete User Guide

## 1. Purpose of this guide

This guide explains how showroom staff, salespeople, managers, marketing users, and administrators should use every menu in Kalasha Fine Jewels CRM. It covers:

- Login and navigation
- Creating, viewing, editing, and deleting records
- Lead assignment and conversion
- Follow-ups and customer retention
- Sales, loyalty points, gift cards, and privilege cards
- Custom orders
- Exhibitions and visitor capture
- Marketing and festival campaigns
- Smart tasks and automation
- Reports, staff, and settings
- Recommended daily operating procedures
- Data-quality and troubleshooting practices

> **Important:** A delete action can permanently remove a visible record. Edit or change its status instead when the record may be required for audit or historical reporting.

---

## 2. Application access

### Production URL

Open the CRM URL supplied by the administrator. For the current hosted installation:

```text
https://kalasha-crm.webignitors.in/
```

### Sign in

1. Open the CRM URL.
2. Enter the email address assigned to your staff account.
3. Enter your password.
4. Select **Sign in**.

If login fails:

- Confirm the email spelling.
- Confirm that Caps Lock is off.
- Ask an administrator whether the staff account is active.
- Ask an administrator to reset the password if necessary.

### Sign out

Select the user name in the top-right header and choose **Sign out**. Always sign out on a shared showroom device.

---

## 3. Navigation and common controls

The left sidebar is divided into six sections:

1. **Overview** — Dashboard and My Smart Work
2. **Relationships** — Leads, Follow-ups, Customers, and Retention Engine
3. **Commerce** — Sales, Custom Orders, Offers, Loyalty, Gift Cards, and Privilege Cards
4. **Growth** — Events & Exhibitions, Marketing Campaigns, Festival Campaigns, and Reports
5. **Operations** — All Smart Tasks, Task Rules, Message Templates, and Manual Tasks
6. **Administration** — Staff Management and Settings

The menu is role-aware. A user only sees modules permitted for their role, and the API independently blocks unauthorized requests.

Standard roles include Super Admin, Management, Sales Manager, Sales Executive, Front Office CRE, Event Manager, and Accountant. Front Office CRE users can maintain leads, follow-ups, and customers but cannot access Sales.

### Install on a phone or tablet

1. Open the CRM in Chrome, Edge, or Safari.
2. Use **Install app** or **Add to Home Screen** from the browser menu.
3. Open **Notifications** and select **Enable device notifications**.
4. Allow notification permission when the browser asks.

The installed CRM uses the same account and role permissions as the browser version.

On a mobile or narrow screen, select the menu icon in the header to open the sidebar.

### Common list controls

Most record lists provide:

- **Search** — Finds records by their searchable name, code, mobile number, or other primary information.
- **Filters** — Limits the list by status, category, priority, source, tier, owner, or type.
- **Add** — Opens the creation form.
- **Eye icon** — Opens the complete record details.
- **Pencil icon** — Edits the record.
- **Trash icon** — Deletes the record after confirmation.

### General CRUD procedure

**Create**

1. Open the required menu.
2. Select **Add**, **Create**, or **Issue**.
3. Complete all fields marked with `*`.
4. Select **Save record**, **Create**, or **Issue card**.

**Read/View**

1. Search or filter the list.
2. Select the eye icon where available.
3. Review profile data, history, linked transactions, and timeline.

**Update**

1. Find the record.
2. Select the pencil icon.
3. Update the required values.
4. Select **Save record** or **Save changes**.

**Delete**

1. Find the record.
2. Select the trash icon.
3. Read the confirmation message.
4. Confirm only when the record is genuinely unwanted or erroneous.

---

## 4. Dashboard

The Dashboard gives management a live overview of the showroom.

### Intelligence and performance

The upper dashboard highlights business signals and sales-team performance. The salesperson table can show:

- Assigned leads
- Open leads
- Converted leads
- Conversion percentage
- Pending follow-ups
- Overdue follow-ups
- Completed follow-ups
- Sales revenue
- Team ranking

Use this area to identify:

- The salesperson with the highest conversion
- Staff with a large unworked lead list
- Staff with overdue follow-ups
- Revenue contribution by salesperson

### Showroom pulse

The operational cards show:

- Total leads
- Today’s and pending follow-ups
- Converted and lost leads
- Total sales
- Pending custom orders
- Gift cards issued
- Exhibition leads
- Loyalty points earned

The charts display monthly sales performance and lead-source distribution. Recent leads and upcoming follow-ups appear below.

### Recommended manager usage

- Review the Dashboard every morning.
- Reassign unattended leads.
- Check overdue follow-ups before examining only revenue.
- Compare conversion quality as well as lead volume.
- Use the source chart to decide where marketing effort should be increased.

---

## 5. My Smart Work

**My Smart Work** converts CRM data into an actionable workday. It answers:

- Who should be contacted?
- Why should they be contacted?
- What should the salesperson say?
- Which product or offer may be relevant?
- What should happen next?

### Summary cards

- **Today** — Tasks due today
- **Overdue** — Tasks past their due time
- **Urgent** — Highest-priority opportunities
- **High priority** — Important tasks requiring attention
- **Completed today** — Work completed today
- **Assigned to me** — Tasks owned by the signed-in user

### Generate smart tasks

1. Select **Generate smart tasks**.
2. Wait while the CRM analyses leads, customers, sales, orders, events, loyalty, and gift-card data.
3. Review the generated groups.

The engine avoids creating a duplicate active task for the same person, reason, type, and due date.

### Work a smart task

1. Open a task card.
2. Review the CRM reason, suggested product, suggested offer, call script, and WhatsApp message.
3. Contact the customer.
4. Select **Add outcome**.
5. Choose an outcome:
   - Interested
   - Not Interested
   - Call Later
   - Purchased
   - Visit Scheduled
   - WhatsApp Sent
   - No Response
   - Wrong Number
6. Add conversation notes.
7. Set the next-action date when required.
8. Select **Complete & create next action**.

Additional actions:

- **Reschedule** — Moves the due date; the new date must be in the future.
- **Skip** — Requires a reason and records why the task was not completed.
- **WhatsApp action** — Opens/logs the prepared WhatsApp communication and moves a pending task into progress.

### Smart-task generation logic

Examples of automatic opportunities include:

- Exhibition lead not contacted within 24 hours
- Hot lead without follow-up for 2 days
- Warm lead without follow-up for 5 days
- Cold lead ready for a soft follow-up after 15 days
- New lead unattended for 24 hours
- Birthday approaching within 15 days
- Anniversary approaching within 30 days
- Family-member birthday approaching
- Customer inactive for 6 or 12 months
- VIP/HNI customer inactive for 90 days
- Post-purchase thank-you after 1 day
- Customer feedback after 7 days
- Partial payment pending
- High-value purchase relationship call
- Custom-order approval, progress, or delivery update
- Loyalty points unused after inactivity
- Gift card expiring within 30 days
- Active festival campaign opportunity

---

## 6. Leads

A lead is a potential customer who has not yet been formally converted into a customer record.

### Create a lead

1. Open **Relationships → Leads**.
2. Select **Add Lead**.
3. Enter:
   - **Name***
   - **Mobile***
   - Email
   - **Assign salesperson***
   - Lead source
   - Status
   - Priority
   - Occasion
   - Minimum and maximum budget
   - Notes
4. Select **Save record**.

### Lead sources

Available sources include:

- Walk-in
- Instagram
- Facebook
- WhatsApp
- Website
- Referral
- Exhibition
- Event
- Phone Call

Always use the correct source because source-level conversion is used in reports.

### Lead priorities

- **Hot** — Strong buying intent or immediate opportunity
- **Warm** — Interested but still considering
- **Cold** — Early-stage or long-term nurturing

### Lead statuses

- **New** — Newly received and not yet worked
- **Contacted** — Initial contact completed
- **Interested** — Interest confirmed
- **Follow-up** — Next interaction required
- **Converted** — Converted into a customer
- **Lost** — Opportunity closed without conversion

### Assign or reassign a salesperson

1. Select the pencil icon on the lead.
2. Choose a staff member in **Assign salesperson**.
3. Save the record.

The assigned salesperson becomes accountable in sales-team conversion and follow-up reporting.

### Search and filter leads

The list can be filtered by:

- Salesperson
- Status
- Priority
- Source

Use filters together, for example: `Salesperson = Aarav`, `Priority = Hot`, `Status = Follow-up`.

### View the lead timeline

Select the eye icon. The lead detail displays:

- Assigned salesperson
- Source, priority, budget, occasion, and interests
- Notes
- Follow-up activity
- Status history and relationship timeline

### Add a lead note

1. Open the lead detail.
2. Select **Add note**.
3. Record the conversation, preference, objection, or agreed next step.
4. Select **Add to timeline**.

Good note example:

```text
Interested in 22K bridal necklace, budget ₹4–5 lakh. Wants to visit Saturday with family. Send three lightweight options on Friday.
```

### Convert a lead into a customer

1. Open the lead detail.
2. Confirm that the mobile number and identity are correct.
3. Select **Convert to customer**.
4. The CRM creates or links a customer and marks the lead as **Converted**.
5. You are redirected to the customer profile.

Do not manually create a duplicate customer before conversion.

### Edit or delete a lead

- Use the pencil icon to update assignment, priority, status, budget, or contact information.
- Use delete only for invalid/test/duplicate records. Prefer **Lost** for a genuine unsuccessful opportunity.

### Export leads

Select **Export** to download lead information in CSV format. The export contains name, mobile, email, source, status, priority, and assigned salesperson.

---

## 7. Follow-ups

Follow-ups track planned calls, WhatsApp conversations, visits, meetings, and emails.

### Calendar

The top calendar shows follow-up counts for today and the next six days. Use it to balance daily workloads.

### Create a follow-up

1. Open **Relationships → Follow-ups**.
2. Select **Add Follow-up**.
3. Enter either the Lead ID or Customer ID, depending on the relationship.
4. Select the type:
   - Call
   - WhatsApp
   - Visit
   - Meeting
   - Email
5. Set the scheduled date and time.
6. Set status, usually **Pending** for a new follow-up.
7. Add notes describing the planned purpose.
8. Save.

### Complete a follow-up

1. Find the follow-up and select the pencil icon.
2. Change status to **Completed**.
3. Enter the outcome.
4. Save.
5. Create another follow-up if the customer agreed to a future action.

### Follow-up statuses

- **Pending** — Still to be completed
- **Completed** — Interaction completed and outcome recorded
- **Cancelled** — No longer required
- **Overdue** — Due date passed without completion

Never mark an unattempted follow-up as completed simply to clear the queue.

---

## 8. Customers

A customer is the central long-term relationship record.

### Create a customer manually

1. Open **Relationships → Customers**.
2. Select **Add Customer**.
3. Enter:
   - Name*
   - Mobile*
   - Email
   - Category
   - Birthday
   - Anniversary
   - City
   - Address
   - Notes
4. Save.

Whenever possible, convert an existing lead instead of manually creating a customer.

### Customer categories

- **Normal** — Standard customer
- **Premium** — Developing high-value relationship
- **VIP** — High-value and priority relationship
- **HNI** — High-net-worth relationship requiring personal attention

### Customer profile

Select the eye icon to review:

- Customer code and contact details
- Category
- Purchase and relationship information
- Loyalty points
- Gift and privilege cards
- Retention information and important dates

### Customer data standards

- Keep one mobile number per person.
- Record birthday and anniversary whenever consent is available.
- Do not create separate customer records for spelling variations.
- Use notes for preferences, not for sensitive payment information.
- Update the category when the relationship level changes.

---

## 9. Retention Engine

The Retention Engine finds reasons to contact existing customers before the relationship becomes inactive.

### Retention opportunities

The dashboard groups opportunities such as:

- Due today
- Occasion opportunities
- Winback customers
- Loyalty and gift-card opportunities
- VIP relationship opportunities
- Cleaning or care reminders

Select **Run retention scan** after importing or updating significant customer and sales data.

### Work a retention message

For each opportunity, review:

- Customer
- Reason
- Occasion and days remaining
- Suggested product
- Suggested offer
- Personalized message

Available workflow actions can include:

- Copy message
- Open WhatsApp
- Mark contacted
- Ignore
- Create follow-up

Each action is recorded for accountability.

### Retention scoring

The CRM calculates a retention opportunity score from customer behavior such as:

- Time since last purchase
- Lifetime value
- Purchase frequency
- Customer category
- Important upcoming dates
- Unused loyalty points
- Gift-card balance
- Engagement history

A high opportunity score means the relationship deserves timely, personal attention; it is not a negative customer rating.

### Customer retention profile

Open a customer’s retention profile to see:

- Retention score and suggested action
- Lifetime value and purchase count
- Loyalty balance
- Gift-card value
- Important dates
- Personalized retention messages
- Purchase context

### Add an important date

1. Open the customer retention profile.
2. Select **Add important date**.
3. Enter the occasion information and date.
4. Save.

Use this for family events and relationship dates that are useful for respectful, consent-based service.

---

## 10. Sales

Sales records generate invoice numbers, update customer value, determine payment status, and award loyalty points.

### Create a sale

1. Open **Commerce → Sales**.
2. Select **Create sale**.
3. Select the customer.
4. Choose the sale date.
5. Enter the responsible Staff ID when applicable.
6. Add one or more jewellery items.

For every item enter:

- Category: Gold, Diamond, Bridal, Silver, Polki, or Kundan
- Jewellery type
- Metal
- Purity
- Gross, net, and stone weight
- Making charge
- Wastage percentage
- Item discount
- Tax
- Item total*

7. Use **Add item** for multiple items.
8. Enter transaction discount and tax.
9. Select payment mode:
   - Cash
   - UPI
   - Card
   - Bank Transfer
   - Cheque
10. Enter the received amount.
11. Verify subtotal and final amount.
12. Select **Complete sale**.

### Automatic sale logic

- Invoice numbers are generated automatically.
- `Paid amount >= Final amount` results in **Paid**.
- A positive payment below the final amount results in **Partial**.
- No payment results in **Pending**.
- Loyalty points are awarded using the configured points-per-₹1,000 rate.
- A partial payment can generate an urgent smart task.
- Purchase data contributes to lifetime value, retention, event attribution, and staff performance.

### Sales limitations in the current user interface

The current Sales screen supports creating and listing sales. Editing an existing invoice is intentionally not exposed in the standard UI. Corrections should follow the administrator’s accounting procedure rather than altering historical transactions casually.

---

## 11. Custom Orders

Custom Orders track made-to-order jewellery from requirement to delivery.

### Create a custom order

1. Open **Commerce → Custom Orders**.
2. Select **Add Custom order**.
3. Enter:
   - Customer ID*
   - Jewellery type*
   - Metal type*
   - Purity
   - Approximate weight
   - Estimated amount*
   - Advance payment
   - Due date*
   - Karigar/vendor
   - Customer approval status
   - Internal notes
4. Save.

### Order statuses

The operational journey is:

```text
New → Designing → Approved → In Production → Ready → Delivered
```

Use **Cancelled** only when the order is formally cancelled.

### Update order progress

1. Open the order using the eye icon.
2. Review customer, order, value, due date, and status history.
3. Select the appropriate next status.
4. Add a note where prompted.

Every status change is stored with:

- Previous status
- New status
- User who made the change
- Date/time
- Optional note

Smart tasks can remind staff about design approval, production updates, ready-for-delivery confirmation, and overdue customer communication.

---

## 12. Offers

Offers define controlled promotional benefits that can be linked to campaigns.

### Create an offer

1. Open **Commerce → Offers**.
2. Select **Add Offer**.
3. Enter:
   - Offer title*
   - Type*
   - Value
   - Start and end date*
   - Customer type
   - Product category
   - Coupon code*
   - Usage limit
   - Status
   - Description
4. Save.

### Offer types

- Discount
- Making Charge Off
- Gift
- Cashback
- Loyalty Bonus

### Offer statuses

- **Active** — Available within its operating dates
- **Inactive** — Manually disabled
- **Expired** — End date passed

Use unique coupon codes and clear descriptions so campaign attribution remains meaningful.

---

## 13. Loyalty

The Loyalty screen displays customer wallets and outstanding points.

### How points are earned

On sale creation:

```text
Points earned = floor(Final sale amount ÷ 1,000 × configured rate)
```

The default displayed rate is 10 points per ₹1,000, but the administrator can change the configured rate in Settings.

### Use the Loyalty screen

1. Open **Commerce → Loyalty**.
2. Search by customer.
3. Review customer category and point balance.

Points cannot go below zero in the underlying loyalty logic.

### Current UI scope

The main Loyalty screen is a wallet overview. Manual credit/debit adjustment exists in the backend but is not currently exposed as a standard button. An administrator should perform controlled adjustments or extend the UI where required.

---

## 14. Gift Cards

Gift Cards hold a monetary balance for a customer.

### Issue a gift card

1. Open **Commerce → Gift Cards**.
2. Select **Add Gift card**.
3. Enter Customer ID when the card is linked to a customer.
4. Enter the original card amount*.
5. Enter the expiry date*.
6. Save.

The CRM generates and displays the card code, original value, remaining balance, and status.

### Gift-card statuses

- **Active**
- **Used**
- **Expired**
- **Cancelled**

### Redemption logic

When a redemption is recorded, the balance decreases. A zero balance changes the card status to **Used**. The backend supports linking a redemption to a sale; the standard list UI currently focuses on issuance and record maintenance.

---

## 15. Privilege Cards

Privilege Cards recognize premium customers with a realistic branded card.

### Dashboard metrics

- Active cards
- Total active privilege value
- Active Diamond members

### Issue a privilege card

1. Open **Commerce → Privilege Cards**.
2. Select **Issue privilege card**.
3. Select the customer*.
4. Select a tier*:
   - Silver
   - Gold
   - Platinum
   - Diamond
5. Enter the privilege amount*.
6. Confirm issue date*.
7. Enter an expiry date when applicable.
8. Select status, normally **Active**.
9. Add internal notes.
10. Review the live card preview.
11. Select **Issue card**.

### Automatic 16-digit card number

The card number is generated automatically and displayed in groups of four digits on the card. Users should never type or reuse a card number manually.

### Card design and information

The visual card contains:

- Kalasha Fine Jewels branding
- Company name from CRM Settings
- Tier styling
- Chip and contactless styling
- 16-digit member number
- Customer name
- Privilege amount
- Issue and expiry information

### Privilege-card statuses

- **Active**
- **Suspended**
- **Expired**
- **Cancelled**

### Edit, suspend, or delete a card

- Select the pencil icon to change tier, amount, dates, status, or notes.
- Prefer **Suspended**, **Expired**, or **Cancelled** to preserve card history.
- Use delete only for an incorrectly created test or duplicate card.

### Search and filters

Search by card, customer, or mobile. Filter by tier and status.

---

## 16. Events & Exhibitions

This module tracks event cost, visitor capture, lead generation, conversion, revenue, and ROI.

### Create an event

1. Open **Growth → Events & Exhibitions**.
2. Select **Create Exhibition / Event**.
3. Enter:
   - Event name*
   - Location*
   - Start and end dates*
   - Stall number
   - Event expense
   - Status
   - Notes
4. Save.

### Event statuses

- upcoming
- active
- completed
- cancelled

### Public visitor capture

1. Open the event detail.
2. Select **Public capture form**.
3. Open the form on a tablet or share the event-specific link/QR workflow.
4. Visitor enters name, mobile, optional email, interests, budget, consent, and notes.

Captured visitors become exhibition leads and are assigned to a salesperson. Returning visitors are detected using contact identity.

### Event detail and analytics

The event detail shows:

- Total and returning visitors
- Converted customers
- Attributed revenue
- Event ROI
- Lead-to-purchase funnel
- Pending follow-ups
- Marketing opportunities
- Attributed transactions
- Visitor-level Customer 360 journey

ROI is calculated as:

```text
ROI % = (Attributed revenue − Event expense) ÷ Event expense × 100
```

### Customer 360

The visitor journey can combine:

- Event attendance
- Transactions
- Follow-ups
- Communications
- Campaigns
- Conversion state
- Lifetime value

Use it before contacting a returning visitor so the conversation reflects the existing relationship.

---

## 17. Marketing Campaigns

The Marketing Command Center creates consent-aware WhatsApp and email audiences.

### Create a marketing campaign

1. Open **Growth → Marketing Campaigns**.
2. Select **Create campaign**.
3. Enter the campaign name.
4. Select delivery channels:
   - WhatsApp
   - Email
5. Choose an audience segment.
6. Apply optional filters such as exhibition, inactivity period, interest, category, or value.
7. Link an offer if applicable.
8. Enter an email subject where required.
9. Write the personalized message.
10. Use supported placeholders such as `{{name}}` and `{{offer}}`.
11. Select **Calculate** to preview the eligible audience.
12. Review the count and sample recipients.
13. Select **Save draft** or **Save & send**.

### Audience types

The system can target:

- Customers
- Leads
- Event visitors
- Returning visitors
- VIP/HNI customers
- Dormant customers
- Upcoming birthday customers
- Upcoming anniversary customers

### Consent logic

The preview automatically excludes people who:

- Have not opted into the selected channel
- Do not have the required mobile number or email

The system limits campaign selection to controlled audience sizes and creates communication logs.

### Delivery note

The CRM records prepared/delivered communication activity. A production WhatsApp Business or email provider must be connected for fully automated external delivery where required.

---

## 18. Festival Campaigns

Festival Campaigns convert seasonal opportunities into targeted retention tasks.

### Create a festival campaign

1. Open **Growth → Festival Campaigns**.
2. Select **Create festival campaign**.
3. Enter:
   - Campaign title*
   - Festival*
   - Start and end dates*
   - Customer type
   - Product interest
   - Offer/value proposition
   - Status
4. Save.

Supported festival/occasion options include:

- Akshaya Tritiya
- Dhanteras
- Diwali
- Karwa Chauth
- Raksha Bandhan
- Wedding Season
- Valentine’s Day
- Mother’s Day

When an **Active** campaign is within its date range, the smart-task engine can create festival opportunity calls for matching customers.

---

## 19. Reports

Open **Growth → Reports** and select a report:

- Lead conversion
- Staff performance
- Sales
- Follow-ups
- Lost leads
- Customer purchase
- Loyalty
- Gift cards
- Custom orders

### Run a report

1. Select the report type.
2. Select **From** and **To** dates.
3. Review the result table.

Report interpretation:

- **Lead conversion** groups total and converted leads by source.
- **Staff performance** compares sale count, revenue, and commission.
- **Lost leads** helps identify lost reasons and coaching opportunities.
- **Customer purchase** ranks customer value.
- **Follow-ups** audits planned and completed relationship activity.

The current **Export CSV** control is presented in the interface, but report-export handling is not yet implemented in the current screen. Lead CSV export is operational from the Leads menu.

---

## 20. All Smart Tasks

This is the complete operational task queue, while My Smart Work emphasizes the signed-in user’s immediate workday.

### Find tasks

Use search and filters for:

- Task/person/reason
- Status
- Priority
- Task type
- Assignee
- Today, overdue, upcoming, or assigned-to-me presets

### Task statuses

- pending
- in_progress
- completed
- skipped

Open a task to review context and record the correct outcome. Do not complete a task without adding meaningful notes where a customer conversation occurred.

---

## 21. Task Rules

Task Rules control which automation rules are active.

The screen groups rules by business module and displays:

- Rule name
- Trigger condition
- Generated task type
- Priority
- Due offset in hours/days
- Enabled/disabled state

### Enable or disable a rule

1. Open **Operations → Task Rules**.
2. Find the required rule.
3. Select its toggle.

Disabling a rule prevents that configured rule from being used for future automation. It does not erase previously created tasks.

Only administrators or designated CRM managers should alter task rules.

---

## 22. Message Templates

Message Templates standardize personalized communication.

### Create a template

1. Open **Operations → Message Templates**.
2. Select **Add template**.
3. Enter:
   - Template title*
   - Message type*
   - Language*
   - Message body*
4. Save.

### Message types

- birthday
- anniversary
- cleaning
- winback
- gift_card
- loyalty
- festival
- vip_invite

### Languages

- Telugu
- Tamil
- English

Use the **Telugu** action on a non-Telugu template to create a Telugu copy for further editing. Always review the translated body before using it.

Use the pencil icon to edit and trash icon to delete a template.

---

## 23. Manual Tasks

Manual Tasks are staff-created reminders that are not necessarily generated by CRM intelligence.

### Create a manual task

1. Open **Operations → Manual Tasks**.
2. Select **Add Task**.
3. Enter:
   - Task title*
   - Description
   - Staff ID
   - Due date and time*
   - Priority
   - Status
   - Notes
4. Save.

### Priorities

- Low
- Medium
- High
- Urgent

### Statuses

- Pending
- In Progress
- Completed
- Cancelled

Use manual tasks for internal work that does not fit a customer intelligence trigger.

---

## 24. Staff Management

### Create a staff account

1. Open **Administration → Staff Management**.
2. Select **Add staff**.
3. Enter:
   - Full name*
   - Email*
   - Temporary password* (minimum eight characters)
   - Role*
   - Branch
4. Select **Create staff account**.
5. Communicate the credentials securely and require the user to protect the password.

The staff list displays name, email, branch, role, and active status.

### Sales assignment

Staff with the relevant sales roles become available in the lead salesperson selector. Accurate role and branch assignment is important for workload assignment and team reporting.

The current Staff screen exposes account creation and overview. Detailed edit/deactivation endpoints exist in the application but are not currently displayed as standard row controls.

---

## 25. Settings

Open **Administration → Settings**.

### Company profile

- Company name
- GST number
- Currency
- GST rate

The company name is used in CRM branding areas such as privilege cards.

### Invoice and loyalty

- Invoice prefix
- Points per ₹1,000

Changes to loyalty rate affect future point awards; historical loyalty records are not recalculated automatically.

### Branches

The screen lists branch name, code, city, and phone.

### Roles and permissions

The screen lists each role, slug, and permission count.

The current settings interface displays branches and roles for reference. Backend support exists for adding branches and updating role permissions, but those editing controls are not exposed on the current screen.

### Save settings

1. Update the required values.
2. Select **Save changes**.
3. Confirm the saved notification.

---

## 26. Recommended daily workflow

### Salesperson

1. Open **My Smart Work**.
2. Complete overdue and urgent tasks first.
3. Review assigned hot leads.
4. Contact new exhibition leads within 24 hours.
5. Record every meaningful outcome.
6. Create the next follow-up before leaving the record.
7. Update lead status and priority.
8. Convert qualified leads promptly.

### Sales manager

1. Review Dashboard salesperson performance.
2. Check unassigned and unattended leads.
3. Review overdue follow-ups.
4. Rebalance lead ownership.
5. Review lost leads and reasons.
6. Confirm partial-payment and custom-order tasks.

### Marketing/retention user

1. Run retention and smart-task scans.
2. Review upcoming birthdays and anniversaries.
3. Review inactive VIP/HNI customers.
4. Preview campaign audiences.
5. Verify channel consent.
6. Use personalized, relationship-first messages.
7. Review event ROI and build follow-up audiences.

### Administrator

1. Maintain active staff accounts and roles.
2. Verify company, tax, invoice, and loyalty settings.
3. Monitor data duplicates.
4. Review automation rules.
5. Ensure scheduled jobs and backups are running.

---

## 27. Data-entry standards

### Mobile numbers

- Use a consistent valid mobile format.
- Do not add descriptive text in the mobile field.
- Search before creating a new lead or customer.

### Names

- Use the customer’s proper name.
- Avoid entries such as “Unknown”, “New Customer”, or only initials where the name is available.

### Notes

A useful note contains:

- What the customer wants
- Budget or product preference
- Objection or concern
- Agreed next action
- Date expectation

Never store card PINs, passwords, CVV values, or unnecessary sensitive personal data.

### Status accuracy

Statuses drive dashboards, reports, automation, and accountability. Update them immediately after the real-world event.

---

## 28. Retention and conversion best practices

- Assign every lead to a salesperson.
- Contact hot and exhibition leads quickly.
- Record outcomes instead of relying on personal memory or WhatsApp history alone.
- Convert leads only after customer identity is confirmed.
- Record birthdays, anniversaries, and preferences with consent.
- Use loyalty and privilege benefits as recognition, not indiscriminate discounting.
- Follow up after purchases and custom-order milestones.
- Use lost-lead data for improvement, not blame.
- Respect WhatsApp/email opt-out choices.
- Review inactive VIP/HNI relationships personally.

---

## 29. Troubleshooting

### Blank page

- Hard refresh with `Cmd + Shift + R` on macOS or `Ctrl + F5` on Windows.
- Confirm the production URL ends in `/crm/`.
- Ask the administrator to verify compiled assets and server routing.

### 401 or returned to login

- The session may have expired.
- Sign in again.
- Ask whether the account remains active.

### Record will not save

- Check all fields marked `*`.
- Verify numeric fields do not contain currency symbols or commas.
- Verify date order and required dates.
- Verify referenced customer/staff IDs exist.
- Read the validation message displayed in the form.

### Record not appearing

- Clear search text and filters.
- Check whether it is on another pagination page.
- Confirm its status/category filter.
- Refresh the screen.

### Smart task not generated

- Confirm the related rule is enabled.
- Confirm the data satisfies the trigger.
- Confirm a duplicate active task does not already exist.
- Run **Generate smart tasks** again after the underlying data is updated.

### Incorrect loyalty points

- Verify the sale final amount.
- Verify the configured points-per-₹1,000 rate.
- Ask an administrator to review the loyalty ledger before making an adjustment.

---

## 30. Current functional boundaries

This guide distinguishes visible functionality from backend capability:

- Lead CSV export is available; lead import exists in the API but has no standard upload control on the current Leads screen.
- Customer Excel import is available from the Customers screen using the downloadable `.xlsx` template.
- Report CSV export is not currently wired to the displayed button.
- Loyalty manual adjustment and gift-card redemption exist in backend services but are not standard buttons on their overview screens.
- Sales creation/listing is available; sale editing is intentionally not exposed.
- Staff creation/listing is available; staff edit/deactivation controls are not displayed.
- Branch creation and role-permission updates are supported by APIs but are not displayed in Settings.
- External WhatsApp/email delivery requires an approved provider integration; the CRM still records communication workflow and consent-aware recipients.

These boundaries should be considered when training users or planning the next development phase.

---

## 31. Quick-reference glossary

| Term | Meaning |
|---|---|
| Lead | Potential customer not yet converted |
| Customer | Confirmed long-term relationship record |
| Conversion | Changing a qualified lead into a customer |
| Follow-up | Planned relationship interaction |
| Retention | Activities intended to maintain and strengthen existing relationships |
| Smart Task | Data-driven action automatically suggested by the CRM |
| Loyalty Wallet | Customer’s points balance and ledger |
| Gift Card | Redeemable monetary-balance card |
| Privilege Card | Premium customer recognition/membership card |
| Lifetime Value | Total recorded customer purchase value |
| Dormant Customer | Customer without a purchase in the configured period |
| ROI | Return on investment for an event or campaign |
| Consent | Customer permission for a communication channel |

---

## 32. Support checklist

When reporting a problem, provide:

1. Menu name
2. Record/customer name or code
3. Action attempted
4. Exact error message
5. Screenshot without passwords or sensitive data
6. Approximate time of the problem
7. Browser and device

Never send database passwords, application keys, customer payment credentials, or full environment configuration in a support message.

---

## 33. Customer Excel entry and automatic categories

Front Office CREs can enter customers individually from a phone/tablet or process a prepared Excel sheet.

### Import an Excel file

1. Open **Customers**.
2. Select **Download template** and keep its column headings unchanged.
3. Enter one customer per row. `Name` and `Mobile` are mandatory.
4. Select **Import Excel**, choose an `.xlsx` or `.xls` file, and confirm.
5. Review the created, updated, skipped, and error counts.

Mobile number is the duplicate key: an existing number is updated and a new number creates a customer. An upload is limited to 5,000 rows. This flow does not use Google Sheets and does not send customer data to Google.

### Automatic customer category

The CRM compares each customer’s recorded lifetime purchase value against active category rules. Default rules are:

- Normal: ₹0–₹49,999.99
- Premium: ₹50,000–₹99,999.99
- HNI: ₹100,000 and above

After a sale, the customer category is recalculated automatically. An administrator can open **Customer Categories**, edit thresholds, enable/disable rules, and select **Recalculate all customers**. A customer marked as a manual category override is excluded from automatic changes.

## 34. Notifications

Open **Notifications** to see daily work summaries and management alerts. Users can mark individual alerts as read or use **Mark all read**.

The scheduled daily summary includes important pending work such as follow-ups and tasks. It is duplicate-safe, so rerunning the scheduler does not create a second summary for the same user and date.

Management users receive important updates inside this module. Interakt and automatic WhatsApp delivery are intentionally excluded; sending WhatsApp without an approved Business API provider is not supported.

## 35. Staff task rewards

Managers can create a task, assign a staff member, set a due date/priority, and define reward points. When the assigned task is marked **Completed**, those points are credited once. Editing or completing the same task again cannot award duplicate points.

Staff can open **My Rewards** to see:

- Current point balance
- Available rewards and required points
- Redemption history and status

To redeem:

1. Choose an affordable active reward.
2. Select **Redeem**.
3. Points are reserved and a redemption request is created.
4. An authorized manager approves or rejects it.
5. Rejection returns the reserved points; approval completes the request and reduces stock where applicable.

Examples in the default reward catalog include a movie ticket, shopping voucher, and team recognition award.

## 36. Offers without coupons

Offers no longer require or expose coupon codes. Every offer must have a **Start Date** and **End Date**, and the end date cannot be earlier than the start date. Use the status and date range to control whether an offer is operational.

## 37. Simplified custom order workflow

Custom orders use only these operational statuses:

- **Processing** — The order is accepted and all design/production work is underway.
- **Order Ready** — Work is complete and the order is ready for customer collection/delivery.
- **Cancelled** — The order will not proceed.

Old detailed production statuses are automatically mapped into this simplified workflow during migration.

## 38. Role-based access rules

Permissions are enforced twice: the frontend hides unavailable menus/actions and the backend rejects unauthorized API calls.

- **Super Admin** — Complete access.
- **Management** — Broad operational and reporting access, excluding destructive staff/settings actions.
- **Sales Manager** — Sales relationship management, performance, campaigns, retention, and reward decisions.
- **Sales Executive** — Assigned leads, follow-ups, customers, sales, tasks, and own rewards.
- **Front Office CRE** — Customer/lead/follow-up entry and updates, tasks, and notifications; no Sales access.
- **Event Manager** — Events, event leads, campaigns, offers, and related reporting.
- **Accountant** — Sales, loyalty, gift cards, and financial reporting.

Access can be refined through role permissions. If a legitimate menu is missing, ask the administrator to check the user’s role instead of sharing another user’s credentials.
