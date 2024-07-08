import { Component } from '@angular/core';
import { LoaderComponent } from '../../../../loader/loader.component';
import { CommonModule, CurrencyPipe } from '@angular/common';
import { GridModule } from '@progress/kendo-angular-grid';
import { Title } from '@angular/platform-browser';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { ExpensesService } from '../../../../../services/expenses.service';
import { ToastrService } from 'ngx-toastr';
import { PayslipService } from '../../../../../services/payslip.service';
import { FormControl, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { ComboBoxModule } from '@progress/kendo-angular-dropdowns';

@Component({
  selector: 'app-people-payrolls',
  standalone: true,
  imports: [LoaderComponent, CommonModule, GridModule, RouterLink, ReactiveFormsModule, CurrencyPipe, ComboBoxModule],
  templateUrl: './people-payrolls.component.html',
  styleUrl: './people-payrolls.component.css'
})
export class PeoplePayrollsComponent {

  public isLoading: boolean = false;

  peopleId: any;
  people_name: any;

  searchFormData: any = null;
  searchPayrollForm!: FormGroup;

  constructor(
    private expenseService: ExpensesService,
    private route: ActivatedRoute,
    private toastr : ToastrService,
    private titleService: Title,
    private paySlipService: PayslipService,
  ) {
    this.titleService.setTitle('Payrolls');
  }

  ngOnInit(): void {
    this.isLoading = true;

    this.peopleId = this.route.snapshot.params['id'];
    this.people_name = this.route.snapshot.params['peopleName'];

    this.loadPayrollBatchNames(this.peopleId);
    this.initializeFormGroup();
    this.loadPeoplePayrolls(this.searchFormData);
  }

  initializeFormGroup(){
    // Initialize Search Expense form
    this.searchPayrollForm = new FormGroup({
      // expense_type: new FormControl(''),
      payroll_batch_name: new FormControl(''),
      status: new FormControl('')
    });
  }


  // LOAD PAYROLL BATCH NAMES IN DROPDOWNS FOR SEARCH
  payrollBatchNames: any[] = [];
  filteredPayrollBatchNames: any[] = [];
  loadPayrollBatchNames(people_id: any): void {
    this.expenseService.getPayrollBatchNames(people_id).subscribe(
      (response: any) => {
        this.payrollBatchNames = response;
        this.filteredPayrollBatchNames = this.payrollBatchNames.slice();
        console.log(this.payrollBatchNames);
      },
      (error: any) => {
        console.log(error.error);
      }
    );
  }

  // FILTERATION OF PAYROLL BATCH NAMES COMBOBOX
  handlePayrollBatchNamesFilter(value: string): void {
    console.log('Filter Value:', value);
    if (value) {
      this.filteredPayrollBatchNames = this.payrollBatchNames.filter(
        (s) => s.payroll_batch_name.toLowerCase().indexOf(value.toLowerCase()) !== -1
      );
    } else {
      this.filteredPayrollBatchNames = this.payrollBatchNames.slice();
    }
    console.log('Filtered Payroll Batch Names:', this.filteredPayrollBatchNames);
  }


  // FUNCTION TO CHECK SEARCH SUBMIT ATLEAST ONE FIELD IS REQUIRED
  isFormEmpty(): boolean {
    const {
      // expense_type,
      payroll_batch_name,
      status,
    } = this.searchPayrollForm.value;
    return (
      // !expense_type &&
      !payroll_batch_name &&
      !status
    );
  }

  // FUNCTION TO RESET FORM AND LOAD EXPENSES
  resetSearchPayrollForm(): void {
    this.isLoading = true;
    this.searchFormData = null;

    this.searchPayrollForm.reset({
      // expense_type: '',
      payroll_batch_name: '',
      status: '',
    });

    this.resetPagination();
    this.loadPeoplePayrolls(this.searchFormData);
  }

  // RESET SKIP PAGE TO ZERO AND LOAD INVOICE ACCORDINGLY
  onSearch(): void {
    this.isLoading = true;
    console.log(this.searchPayrollForm.value);
    this.searchFormData = this.searchPayrollForm.value;

    if(this.searchFormData.payroll_batch_name === undefined) {
      this.searchFormData.payroll_batch_name = '';
    }

    console.log(this.searchFormData);

    this.resetPagination();
    this.loadPeoplePayrolls(this.searchFormData);
  }

  // TO RESET PAGE TO 1
  resetPagination() {
    this.skip = 0;
  }


  // KENDO GRID DATA

  gridloading = false;

  public gridData: any = { data: [], total: 0 }
  pageSize = 10;
  skip = 0;
  total = 0;

  //FUNCTION IF PAGE CHANGE TRIGGERED
  pageChange(event: any) {
    //update kendo page change skip and pageSize
    this.skip = event.skip;
    this.pageSize = event.take;
    this.loadPeoplePayrolls(this.searchFormData);
  }

  // TO LOAD ALL PAYROLLS FOR THE PEOPLE
  loadPeoplePayrolls(searchFormData: any) {
    this.gridloading = true;

    this.expenseService.getPeoplePayrolls(this.peopleId, this.skip, this.pageSize, searchFormData).subscribe(
      (response: any) => {
        console.log(response);

        this.gridData = {
          data: response.payrollData,
          total: response.total
        }

        this.gridloading = false;
        this.isLoading = false;
      },
      (error) => {
        console.log(error.error);
        this.toastr.error(error.error.message);

        this.gridData = {
          data: [],
          total: 0
        };

        this.gridloading = false;
        this.isLoading = false;
      }
    );

  }

  // FUNCTION TO VIEW PAYSLIP
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
        this.gridloading = false;
      },
      (error) => {
        console.error('Error loading PDF:', error);
        if (error.toaster_error) {
          console.log('Toaster error:', error.toaster_error);
        }
        this.gridloading = false;
      }
    );
  }

  // FUNCTION TO DOWNLOAD PAYSLIP
  downloadPdf(id: any): void {
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
          a.download = `payslip_${id}.pdf`; // Set the file name
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
          URL.revokeObjectURL(fileURL); // Clean up
        }
        this.gridloading = false;
      },
      (error) => {
        console.error('Error downloading PDF:', error);
        if (error.toaster_error) {
          console.log('Toaster error:', error.toaster_error);
        }
        this.gridloading = false;
      }
    );
  }

  // FUNCTION TO PARSE THE PDF BLOB FILE
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

  isDisabled: { [id: number]: boolean } = {};

  //FUNCTION FOR SENDING MAIL
  sendMail(dataItem: any) {
    console.log(dataItem);
    this.isDisabled[dataItem.payroll_history_id] = true; // Disable the button
    this.toastr.info('Sending mail in progress.....');

    this.paySlipService.sendMail(dataItem.payroll_history_id).subscribe(
      (response) => {
        console.log(response);
        this.toastr.success(response.message);
        this.isDisabled[dataItem.payroll_history_id] = false; // Enable the button on success
      },
      (error) => {
        console.error(error);
        const errorMessage = error.error.message;
        this.toastr.error(`Failed to send Mail to ${dataItem.people_name}: ${errorMessage}`);
        this.isDisabled[dataItem.payroll_history_id] = false; // Enable the button on error
      }
    );
  }


}
