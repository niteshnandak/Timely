import { RouterLink } from "@angular/router"

export const adminNavbarData = [
    {
        routeLink: 'organisation' ,
        icon: 'bi bi-buildings-fill',
        label: 'Organisations',
    }
]

export const navbarData = [
    {
        routeLink: 'dashboard' ,
        icon: 'bi bi-clipboard2-data-fill',
        label: 'Dashboard',
    },
    {
        routeLink: 'companies',
        icon: 'fas fa-building',
        label: 'Companies',
    },
    {
        routeLink: 'people',
        icon: 'fas fa-user-friends',
        label: 'People'
    },
    {
        routeLink: 'logout',
        icon: 'fas fa-sign-out-alt',
        label: 'Logout',
        isBottom: true
    }

]
export const companyNavData = [

        {
            routeLink: 'company/company-details/customers',
            icon: 'fas fa-users',
            label: 'Customers'
        },
        {
            routeLink: 'company/company-details/assignments',
            icon: 'fas fa-clipboard-list',
            label: 'Assignments'
        },
        {
            routeLink: 'company/company-details/timesheets',
            icon: 'bi bi-calendar-week',
            label: 'Timesheet'
        },
        {
            routeLink: 'company/company-details/invoices',
            icon: 'bi bi-receipt',
            label: 'Invoices'
        },
        {
          routeLink: 'company/company-details/expenses',
          icon: 'fas fa-coins',
          label: 'Expenses'
        },
        {
            routeLink: 'company/company-details/payroll-batch',
            icon: 'fas fa-file-invoice-dollar',
            label: 'Payroll'
        },
        {
            routeLink: 'company/company-details/payroll-history',
            icon: 'fas fa-scroll',
            label: 'Payroll History'
        },

]
