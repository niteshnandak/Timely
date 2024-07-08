import { Routes } from '@angular/router';
import { CompanyGridComponent } from './components/company/company-grid/company-grid.component';

import { OrganisationDashboardComponent } from './components/afa-admin/features/organisation/organisation-dashboard/organisation-dashboard.component';
import { CompanyDetialComponent } from './components/company/company-detail/company-detail.component';
import { CompanySettingsComponent } from './components/company/company-settings/company-settings.component';
import { RegistrationComponent } from './auth/registration/registration.component';
import { SavePasswordComponent } from './auth/save-password/save-password.component';
import { PeopleDashboardComponent } from './components/afa-admin/features/people/people-dashboard/people-dashboard.component';
import { OrganisationSettingsComponent } from './components/afa-admin/features/organisation/organisation-settings/organisation-settings.component';
import { AfaPageComponent } from './components/afa-admin/afa-page/afa-page/afa-page.component';
import { AssignmentDashboardComponent } from './components/afa-admin/features/assignment/assignment-dashboard/assignment-dashboard.component';
import { companyNavData } from './components/side-navbar/nav-data';
import { AddCompanyComponent } from './components/company/add-company/add-company.component';
import { SearchCompanyComponent } from './components/company/search-company/search-company.component';
import { AuthmanageComponent } from './auth/authmanage/authmanage.component';
import { CustomerGridComponent } from './components/afa-admin/features/customers/customer-grid/customer-grid.component';
import { AddPeopleComponent } from './components/afa-admin/features/people/add-people/add-people.component';
import { SearchPeopleComponent } from './components/afa-admin/features/people/search-people/search-people.component';
import { AddAssignmentComponent } from './components/afa-admin/features/assignment/add-assignment/add-assignment.component';
import { SearchAssignmentComponent } from './components/afa-admin/features/assignment/search-assignment/search-assignment.component';
import { LoginComponent } from './auth/login/login.component';
import { userGuard } from './auth/guards/user.guard';
import { EditPeopleComponent } from './components/afa-admin/features/people/edit-people/edit-people.component';
import { AppAdminpageComponent } from './components/application-admin/app-adminpage/app-adminpage.component';
import { AdminAfausersComponent } from './components/application-admin/admin-afausers/admin-afausers.component';
import { AddCustomerComponent } from './components/afa-admin/features/customers/add-customer/add-customer.component';
import { EditCustomerComponent } from './components/afa-admin/features/customers/edit-customer/edit-customer.component';
import { adminGuard } from './auth/guards/admin.guard';
import { AdminOrganisationComponent } from './components/application-admin/admin-organisation/admin-organisation.component';
import { SearchAfausersComponent } from './components/application-admin/admin-afausers/search-afausers/search-afausers.component';
import { AddAfausersComponent } from './components/application-admin/admin-afausers/add-afausers/add-afausers.component';
import { EditAfausersComponent } from './components/application-admin/admin-afausers/edit-afausers/edit-afausers.component';
import { AddOrganisationComponent } from './components/application-admin/admin-organisation/add-organisation/add-organisation.component';
import { EditOrganisationComponent } from './components/application-admin/admin-organisation/edit-organisation/edit-organisation.component';
import { SearchOrganisationComponent } from './components/application-admin/admin-organisation/search-organisation/search-organisation.component';
import { SearchCustomersComponent } from './components/afa-admin/features/customers/search-customers/search-customers.component';
import { EditAssignmentComponent } from './components/afa-admin/features/assignment/edit-assignment/edit-assignment.component';
import { OrganisationDetailsComponent } from './components/application-admin/admin-organisation/organisation-details/organisation-details.component';
import { ExpensesComponent } from './components/afa-admin/features/expenses/expenses.component';
import { TimesheetDashboardComponent } from './components/afa-admin/features/timesheets/timesheet-dashboard/timesheet-dashboard.component';
import { TimesheetDetailsComponent } from './components/afa-admin/features/timesheets/timesheet-details/timesheet-details.component';
import { UploadTimesheetComponent } from './components/afa-admin/features/timesheets/upload-timesheet/upload-timesheet.component';
import { PayrollBatchGridComponent } from './components/afa-admin/features/Payroll/payroll-batch-grid/payroll-batch-grid.component';
import { PayrollVerifyInvoicesComponent } from './components/afa-admin/features/Payroll/payroll-verify-invoices/payroll-verify-invoices.component';
import { InvoiceComponent } from './components/afa-admin/features/invoice/invoice.component';
import { PayrollSelectionComponent } from './components/afa-admin/features/Payroll/payroll-selection/payroll-selection.component';
import { PeopleAssignmentComponent } from './components/afa-admin/features/people/people-assignment/people-assignment.component';
import { PayrollHistoryComponent } from './components/afa-admin/features/Payroll/payroll-history/payroll-history.component';
import { PayrollDetailComponent } from './components/afa-admin/features/Payroll/payroll-detail/payroll-detail.component';
import { PeopleInvoiceComponent } from './components/afa-admin/features/people/people-invoice/people-invoice.component';
import { PeopleExpensesComponent } from './components/afa-admin/features/people/people-expenses/people-expenses.component';
import { MappingGridComponent } from './components/afa-admin/features/timesheets/mapping-grid/mapping-grid.component';
import { PeoplePayrollsComponent } from './components/afa-admin/features/people/people-payrolls/people-payrolls.component';

export const routes: Routes = [
  { path: '', redirectTo: 'login', pathMatch: 'full' },
  { path: 'login', component: AuthmanageComponent },
  { path: 'registration', component: AuthmanageComponent },
  { path: 'forgot-password', component: AuthmanageComponent },
  { path: 'set-password/:token', component: AuthmanageComponent },
  { path: 'reset-password/:token', component: AuthmanageComponent },

  {
    path: 'app-admin',
    component: AppAdminpageComponent,
    canActivate: [adminGuard],
    children: [
      {
        path: 'organisation',
        component: AdminOrganisationComponent,
        children: [
          {
            path: 'search-organisation',
            component: SearchOrganisationComponent,
          },
          {
            path: 'add-organisation',
            component: AddOrganisationComponent,
          },
          {
            path: 'edit-organisation/:id',
            component: EditOrganisationComponent,
          },
        ],
      },
      {
          path: 'organisation/:orgId/details',
          component:OrganisationDetailsComponent,
      },
      {
        path: 'organisation/:orgId/afausers',
        component: AdminAfausersComponent,
        children: [
          {
            path: 'search-afausers',
            component: SearchAfausersComponent,
          },
          {
            path: 'add-afausers',
            component: AddAfausersComponent,
          },
          {
            path: 'edit-afausers/:userId',
            component: EditAfausersComponent
          },
        ],
      },
    ],
  },

  {
    path: '',
    component: AfaPageComponent,
    canActivate: [userGuard],
    children: [
      {
        path: 'dashboard',
        component: OrganisationDashboardComponent,
      },
      {
        path: 'organisation/settings',
        component: OrganisationSettingsComponent,
      },
      {
        path: 'companies',
        loadComponent: () =>
          import(
            './components/company/company-grid/company-grid.component'
          ).then((m) => m.CompanyGridComponent),
        children: [
          {
            path: 'add-company',
            component: AddCompanyComponent,
          },
          {
            path: 'search-company',
            component: SearchCompanyComponent,
          },
        ],
      },
      {
        path: 'company/company-details',
        component: CompanyDetialComponent,
      },
      {
        path: 'company/company-settings',
        component: CompanySettingsComponent,
      },
      {
        path: 'company/company-details/assignments',
        component: AssignmentDashboardComponent,
        children: [
          {
            path: 'add-assignment',
            component: AddAssignmentComponent,
          },
          {
            path: 'edit-assignment/:id',
            component: EditAssignmentComponent,
          },
          {
            path: 'search-assignment',
            component: SearchAssignmentComponent,
          },
        ],
      },
      {
        path: 'company/company-details/payroll-batch',
        component: PayrollBatchGridComponent,
      },
      {
        path: 'company/company-details/payroll-history',
        component: PayrollHistoryComponent,
      },
      {
        path: 'company/company-details/payroll-batch/:id',
        children:[
          {
            path: 'select-invoices',
            component: PayrollSelectionComponent
          },
          {
            path: 'verify-invoices',
            component: PayrollVerifyInvoicesComponent
          },
          {
            path: 'payroll-details',
            component: PayrollDetailComponent
          }
        ]
      },

      {
         path: 'company/company-details/payroll-verify-invoices',
         component:PayrollVerifyInvoicesComponent
      },
      {
        path: 'company/company-details/timesheets',
        component: TimesheetDashboardComponent,
      },
      {
        path: 'company/company-details/timesheets/:id/timesheet-details',
        component: TimesheetDetailsComponent,
      },
      {
        path: 'company/company-details/:id/uploaded-timesheet',
        component: UploadTimesheetComponent,
      },
      {
        path: 'company/company-details/expenses',
        component: ExpensesComponent,
      },
      {
        path: 'company/company-details/customers',
        component: CustomerGridComponent,
        children: [
          {
            path: 'search-customer',
            component: SearchCustomersComponent,
          },
          {
            path: 'add-customer',
            component: AddCustomerComponent,
          },
          {
            path: 'edit-customer/:id',
            component: EditCustomerComponent,
          },
        ],
      },
      {
        path: 'company/company-details/invoices',
        component: InvoiceComponent,
      },
      {
        path: 'people',
        component: PeopleDashboardComponent,
        children: [
          {
            path: 'add-people',
            component: AddPeopleComponent,
          },
          {
            path: 'search-people',
            component: SearchPeopleComponent,
          },
          {
            path: 'edit-people/:id',
            component: EditPeopleComponent,
          },
        ],
      },
      {
        path: 'people/assignment/:id/:peopleName',
        component: PeopleAssignmentComponent,
      },
      {
        path: 'people/invoice/:id/:peopleName',
        component: PeopleInvoiceComponent,
      },
      {
        path: 'people/payrolls/:id/:peopleName',
        component: PeoplePayrollsComponent
      },
      {
        path: 'people/expenses/:id',
        component: PeopleExpensesComponent,
      },
    ],
  },

  { path: '**', redirectTo: 'login', pathMatch: 'full' },
];
