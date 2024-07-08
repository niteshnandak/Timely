import { Routes } from '@angular/router';
import { TimesheetDashboardComponent } from './Timesheets/timesheet-dashboard/timesheet-dashboard.component';
import { TimesheetDetailComponent } from './Timesheets/timesheet-detail/timesheet-detail.component';
import { InvoiceHomeComponent } from './Invoices/invoice-home/invoice-home.component';
import { CheckCsvComponent } from './Timesheets/check-csv/check-csv.component';
import { RegisterComponent } from './components/users/register/register.component';
import { LoginComponent } from './components/users/login/login.component';
import { SetPasswordComponent } from './components/users/set-password/set-password.component';
import { ForgotPasswordComponent } from './components/users/forgot-password/forgot-password.component';
import { ResetPasswordComponent } from './components/users/reset-password/reset-password.component';
import { AuthGuardService } from './services/auth-guard.service';
import { AuthRedirectGuardService } from './services/auth-redirect-guard.service';
import { CheckReportsComponent } from './Invoices/check-reports/check-reports.component';

export const routes: Routes = [



  {
    path: '', redirectTo: "login", pathMatch: 'full'
  },
  { path: 'register',
    component: RegisterComponent },
  {
    path: 'login',
    component: LoginComponent,
    // canActivate: [AuthRedirectGuardService]
  },
  {
    path: 'set-password/:token',
    component: SetPasswordComponent
  },

  {
    path: 'forgot-password',
    component: ForgotPasswordComponent
  },
  {
    path: 'reset-password/:token',
    component: ResetPasswordComponent
   //  canActivate: [AuthGuardService]
  },
  {
    path: "timesheet",
    component:TimesheetDashboardComponent,
     canActivate: [AuthGuardService]
  },
  {
    path: "timesheet/:id",
    component:TimesheetDetailComponent,
    canActivate: [AuthGuardService]
  },
  {
    path: "invoices",
    component : InvoiceHomeComponent,
    canActivate: [AuthGuardService]
  },
  {
    path: "timesheet/check-csv/:fileId/:timesheetId/:noOfRows",
    component : CheckCsvComponent,
    canActivate: [AuthGuardService]
  },
  {
    path: "invoices/download-reports",
    component: CheckReportsComponent,
    canActivate: [AuthGuardService]
  },

  { path: '**', component: LoginComponent }


];
