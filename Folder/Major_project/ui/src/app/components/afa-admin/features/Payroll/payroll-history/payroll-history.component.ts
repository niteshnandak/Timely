import { CommonModule, CurrencyPipe } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { GridModule, PageChangeEvent } from '@progress/kendo-angular-grid';
import { CompanyService } from '../../../../../services/company.service';
import { AuthService } from '../../../../../auth/auth-services/auth.service';
import { PayslipService } from '../../../../../services/payslip.service';
import { PeopleService } from '../../../../../services/people.service';
import { PayrollService } from '../../../../../services/payroll.service';
import { ToastrService } from 'ngx-toastr';
import { Title } from '@angular/platform-browser';

@Component({
  selector: 'app-payroll-history',
  standalone: true,
  imports: [GridModule, RouterLink, ReactiveFormsModule, CommonModule, CurrencyPipe],
  templateUrl: './payroll-history.component.html',
})
export class PayrollHistoryComponent implements OnInit {
  public gridData: any = {
    data: [],
    total: 0,
  };
  payrollHistory: any[] = [];
  public totalRecords: number = 0;
  public loading: boolean = false;
  public skip: number = 0;
  public pageSize: number = 10;
  public companyName: string = '';
  public showForm = false;
  isSearchActive: boolean = false;
  searchPayrollHistoryForm: any;
  total: number = 0;
  payslip: any[] = [];
  currentPage: number = 1;
  perPage: number = 10;
  gridloading = false;
  isMailDisabled: { [id: number]: boolean } = {};
  isRollbackDisabled: { [id: number]: boolean } = {};

  constructor(
    private paySlipService: PayslipService,
    private companyService: CompanyService,
    private authService: AuthService,
    private router: Router,
    private peopleService: PeopleService,
    private payrollService: PayrollService,
    private toastService: ToastrService,
    private TitleService: Title
  ) {
    TitleService.setTitle("Payroll History");
  }

  ngOnInit(): void {
    this.initializeFormGroup();
    this.loadPayrollHistory();
    this.loadCompanyName();
  }

  initializeFormGroup() {
    this.searchPayrollHistoryForm = new FormGroup({
      people_name: new FormControl(''),
      payroll_batch_name: new FormControl(''),
    });
  }

  isFormEmpty(): boolean {
    const {
      people_name,
      payroll_batch_name,
    } = this.searchPayrollHistoryForm.value;
    return (
      !people_name &&
      !payroll_batch_name
    );
  }

  searchFormData: any = null;
  resetSearchPayrollHistoryForm(): void {
    this.searchFormData = null;
    this.gridloading = true;

    this.searchPayrollHistoryForm.reset({
      people_name: '',
      payroll_batch_name: ''
    });

    this.loadPayrollHistory();
  }

  // On submitting the Search form
  searchPayrollHistoryFormSubmit() {
    const companyId = this.companyService.getStoredCompanyId();
    this.isSearchActive = true;
    this.gridloading = true;
    console.log(this.searchPayrollHistoryForm.value);
    this.paySlipService.searchPayslip(this.currentPage, this.perPage, this.searchPayrollHistoryForm.value, companyId).subscribe(
      (result: any) => {

        this.gridData = {
          data: result.data,
          total: result.total,
        };
        // this.payslip = result.result;
        // this.total = result.total;
        this.gridloading = false;
      }, (error) => {
        this.gridData = {
          data: [],
          total: 0
        };
        console.error('Error searching timesheets', error);
        this.gridloading = false;
      });
  }

  loadCompanyName(): void {
    const companyId = this.companyService.getStoredCompanyId();
    if (companyId) {
      this.companyService.getCompanyId(companyId).subscribe({
        next: (response) => {
          this.companyName = response.company_name;
          console.log(companyId);
        },
        error: (error) => {
          console.error('Error loading company name');
        }
      });
    }
  }

  onPageChange(event: PageChangeEvent): void {
    console.log('Page change event:', event);
    this.skip = event.skip;
    this.pageSize = event.take;
    this.loadPayrollHistory();
  }

  loadPayrollHistory(): void {
    this.loading = true;
    const companyId = this.companyService.getStoredCompanyId();
    const organisationId = this.authService.getUser()['organisation_id'];
    // const peopleName = this.peopleService.getAllPeople()['people_name']
    // console.log(companyId, organisationId);

    this.paySlipService.getPayrollHistory(this.skip, this.pageSize, organisationId, companyId).subscribe({
      next: response => {
        console.log('Data loaded:', response);
        this.gridData = {
          data: response.data,
          total: response.total,
        };
        this.loading = false;
      },
      error: error => {
        console.error('Error loading data:', error);
        this.loading = false;
      }

    });
  }

  viewPdf(id: any): void {
    this.gridloading = true;
    if (!id) {
      console.error('Invalid payroll history ID provided.');
      return;
    }

    console.log('Requesting PDF for ID:', id);

    this.paySlipService.viewPdf(id).subscribe(
      (response: Blob) => {
        // Check if response is Blob or JSON (error)
        if (response.type === 'application/json') {
          this.parseBlob(response); // Handle JSON error response
        } else {
          const fileURL = URL.createObjectURL(response);
          window.open(fileURL, '_blank');
        }
      },
      (error) => {
        console.error('Error loading PDF:', error);
        if (error.toaster_error) {
          console.log('Toaster error:', error.toaster_error);
        }
      }
    );
    this.loadPayrollHistory();
  }

  private parseBlob(blob: Blob): void {
    const reader = new FileReader();
    reader.onload = () => {
      try {
        const jsonData = JSON.parse(reader.result as string);
        console.error('Error JSON Data:', jsonData);
        // Handle the JSON error data (e.g., display error message to the user)
      } catch (e) {
        console.error('Error parsing JSON:', e);
      }
    };
    reader.readAsText(blob);
  }

  downloadPdf(id: any, peopleName: string): void {
    this.gridloading = true;
    if (!id) {
      console.error('Invalid payroll history ID provided.');
      return;
    }
    console.log('Requesting PDF for download for ID:', id);
  
    this.paySlipService.viewPdf(id).subscribe(
      (response: Blob) => {
        // Check if response is Blob or JSON (error)
        if (response.type === 'application/json') {
          this.parseBlob(response); // Handle JSON error response
        } else {
          const fileURL = URL.createObjectURL(response);
          const a = document.createElement('a');
          a.href = fileURL;
  
          // Get the current timestamp
          const now = new Date();
          const year = now.getFullYear();
          const month = ('0' + (now.getMonth() + 1)).slice(-2); // Adding leading zero
          const day = ('0' + now.getDate()).slice(-2); // Adding leading zero
          const hours = ('0' + now.getHours()).slice(-2); // Adding leading zero
          const minutes = ('0' + now.getMinutes()).slice(-2); // Adding leading zero
          const seconds = ('0' + now.getSeconds()).slice(-2); // Adding leading zero
          const timestamp = `${year}${month}${day}_${hours}${minutes}${seconds}`;
  
          // Create the filename with people's name and timestamp
          const filename = `payslip_${peopleName}_${timestamp}.pdf`;
  
          a.download = filename; // Set the file name
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
          URL.revokeObjectURL(fileURL); // Clean up
        }
      },
      (error) => {
        console.error('Error downloading PDF:', error);
        if (error.toaster_error) {
          console.log('Toaster error:', error.toaster_error);
        }
      }
    );
    this.loadPayrollHistory();
  }
  

  disableButtonCondition(dataItem: any) { }

  //FUNCTION FOR SENDING MAIL
  sendMail(dataItem: any) {
    console.log(dataItem);
    this.isMailDisabled[dataItem.payroll_history_id] = true; // Disable the button
    this.toastService.info('Sending mail in progress.....');

    this.paySlipService.sendMail(dataItem.payroll_history_id).subscribe(
      (response) => {
        console.log(response);
        this.toastService.success(response.message);
        this.isMailDisabled[dataItem.payroll_history_id] = false; // Enable the button on success
      },
      (error) => {
        console.error(error);
        const errorMessage = error.error.message;
        this.toastService.error(`Failed to send Mail to ${dataItem.people_name}: ${errorMessage}`);
        this.isMailDisabled[dataItem.payroll_history_id] = false; // Enable the button on error
      }
    );
  }




  rollback(dataItem:any,payroll_batch_id: any, people_id: any) {
    console.log('hello')
    this.isRollbackDisabled[dataItem.payroll_history_id] = true;
    console.log(payroll_batch_id, people_id);
    this.payrollService.rollBackPayrollBatch(payroll_batch_id, people_id).subscribe({
      next: (response: any) => {  
        this.toastService.success(response['toaster_success'], 'Success');
        this.loadPayrollHistory();
        console.log(response);
        this.isRollbackDisabled[dataItem.payroll_history_id] = false;
      },
      error: (msg) => {
        this.toastService.error(msg.error["toaster_error"], 'Error');
        console.log(msg);
      }
    });
  }
}
