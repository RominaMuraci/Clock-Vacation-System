# Timeclock & Holiday Management System - User Manual

## Table of Contents
1. [Dashboard](#dashboard)
2. [Clock System](#clock-system)
3. [Timeclock Table](#timeclock-table)
4. [Timeline](#timeline)
5. [Request Leave](#request-leave)
6. [Requested Off Days](#requested-off-days)
7. [Leave Table](#leave-table)
8. [Holidays](#holidays)
9. [Settings](#settings)
10. [Billing System](#billing-system)

---

## 1. Dashboard

On the dashboard, you can find two tables: Absent Employee Table and Sick Employee Table.  
You can also check the period of time by using the start and end date filters to view specific time ranges for each employee.

---

## 2. Clock System

To clock in, simply press the "Clock In" button. To clock out, press the "Clock Out" button.  
You can also start and end breaks during the day:
- **Start Break:** Click on "Start Break" when you begin your break.
- **End Break:** Click on "End Break" when you finish your break.

After you clock in, the system records your working hours automatically until you clock out.

---

## 3. Timeclock Table

The Timeclock Table provides a detailed record of employee working hours, including clock-in and clock-out times, breaks, and total hours worked. Users can search and filter the table using various fields to find specific records.

### Table Columns:
- **Employee ID:** A unique identifier assigned to each employee.
- **Day:** The date of the recorded work entry.
- **Employee:** The full name of the employee.
- **Clock In:** The exact time when the employee clocked in.
- **Clock Out:** The exact time when the employee clocked out.
- **Breaks:** The number of breaks taken during the shift.
- **Total Hours/h:** The total working hours recorded for the day.
- **Break Duration:** The total duration of breaks taken.
- **Daily Total/h:** The total hours worked after accounting for breaks.
- **Regular Hours/h:** The number of regular working hours.
- **Overtime/h:** Any extra hours worked beyond regular hours.
- **Notes:** Additional comments or details related to the work entry.
- **Actions:** Provides options to edit or manage the time entry.

---

## 4. Timeline

The timeline feature shows a detailed breakdown of your workday, including when you clocked in and out.

---

## 5. Request Leave

Employees can request leave by filling out the leave request form. The form includes several fields to ensure accurate leave tracking and approval.

### Leave Request Form Fields:
- **Overdue Days from Last Year:** Displays any unused leave days carried over from the previous year.
- **Quota:** Shows the total number of leave days the employee is entitled to for the current year.
- **Used Days:** Indicates the number of leave days already taken by the employee.
- **Remaining:** Displays the number of leave days still available for use.

### Fields to Fill Out When Requesting Leave:
- **Select an employee:** Choose the employee for whom the leave request is being submitted (visible only to admins or managers).
- **Leave type:** Select the type of leave from the available options:
  - **Paid Off:** Typically used for extended holidays, such as summer vacations or long breaks.
  - **Sick Leave:** If the employee provides a medical report, these days are not deducted from the leave balance.
  - **School Reason:** Used for academic-related absences (e.g., exams, university obligations).
  - **Others:** For any other reasons such as personal matters, emergencies, or special occasions.
- **Start Date:** Choose the date when the leave will begin.
- **End Date:** Choose the date when the leave will end.
- **Send To:** Select the recipient(s) who should receive the leave request for approval.
- **Reason:** Provide a brief explanation for the leave request.

Once the form is completed, the employee submits the request for review.

---

## 6. Requested Off Days

*Section for future reference or addition.*

---

## 7. Leave Table

The Leave Table provides a comprehensive view of all leave requests submitted by employees, along with their current leave balance and usage history.

### Columns in the Leave Table:
- **Employee:** The name of the employee.
- **Hire Date:** The date the employee joined the company.
- **Year:** The year for which leave balance is being calculated.
- **Quota:** The total number of leave days allocated to the employee.
- **Overdue Days:** Any unused leave days carried over from previous years.
- **Used:** The number of leave days the employee has already taken.
- **Remaining:** The number of leave days still available for use.

#### For New Employees:
The remaining leave is calculated proportionally based on the number of days worked in the current year:
Remaining = (Quota / 365) * (Current Date - Hire Date) + Forward Days - Used

#### For Old Employees:
Employees who have completed at least one full year of work follow this formula:
Remaining = Forward Days + Quota - Used 

## 8. Holidays

Admins can manage holidays through the "Holidays" section, where they can:

- **Enable Holiday Flag:** A toggle option to enable or disable holiday management for the current year.
- **Adding a New Holiday Manually:**
  - **Select Holiday Date:** Choose the date of the holiday.
  - **Holiday Name:** Enter the name of the holiday.
  - **Save:** Save the holiday to update the system records.

- **Bulk Uploading Holidays via CSV:**
  - **Upload Holidays CSV:** Allows admins to upload a list of holidays in bulk using a CSV file.
  - **File Format:** The CSV must contain two columns: Date (YYYY-MM-DD format) and Holiday Name.
  - **Example:**
    ```csv
    2024-01-01, Festat e Vitit te Ri
    ```
- **Viewing and Managing Holidays:**
  The **Holidays Table** lists all previously added holidays, displaying:
  - **Date:** The specific date of the holiday.
  - **Holiday Name:** The title or reason for the holiday.
  - **Actions:** Options to delete a holiday if needed.
---
## 9. Settings
The settings section allows admins to adjust system configurations:
- **Current Year Total Vacation Days:** Displays the annual quota assigned to employees.
- **Who can make requests for others?** Allows admins to specify who can make leave requests on behalf of employees.
- **Who can approve requests?** Shows the users who have the authority to approve leave requests.
---
## 10. Billing System
The billing system allows you to be back on the system.
---
*This manual provides all the necessary instructions to efficiently use the Timeclock & Holiday Management System.* 
