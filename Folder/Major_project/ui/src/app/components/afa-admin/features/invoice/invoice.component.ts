import { CommonModule, formatDate } from '@angular/common';
import { Component } from '@angular/core';
import { GridModule } from '@progress/kendo-angular-grid';
import { LoaderComponent } from '../../../loader/loader.component';
import { RouterLink, Router, ActivatedRoute } from '@angular/router';
import { Title } from '@angular/platform-browser';
import { ToastrService } from 'ngx-toastr';
import { InvoiceService } from '../../../../services/invoice.service';
import {
  ReactiveFormsModule,
  Validators,
  FormGroup,
  FormControl,
  FormBuilder,
  FormArray,
  AbstractControl,
  ValidationErrors,
} from '@angular/forms';
import { Modal } from 'bootstrap';

@Component({
  selector: 'app-invoice',
  standalone: true,
  imports: [
    GridModule,
    CommonModule,
    LoaderComponent,
    RouterLink,
    ReactiveFormsModule,
  ],
  providers: [InvoiceService],
  templateUrl: './invoice.component.html',
  styleUrl: './invoice.component.css',
})

// CLASS INVOICECOMPONENT
export class InvoiceComponent {
  public isLoading!: boolean;
  isGridLoading = false;
  pageSize = 10;
  skip = 0;
  total = 0;
  public gridData: any = { data: [], total: 0 };
  searchFormData: any = {};
  companyId!: any;
  companyName!: any;
  invoiceForm!: FormGroup;
  editInvoiceForm!: FormGroup;
  assignments: any[] = [];
  deleteInvId: any;
  modal: any;
  editModal: any;
  editInvID!: number;

  // CONSTRUCTOR
  constructor(
    private router: Router,
    private toastr: ToastrService,
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private titleService: Title,
    private invoiceService: InvoiceService
  ) {
    this.titleService.setTitle('invoices');
    this.companyId = this.invoiceService.getStoredCompanyId();

  }

  // NGONINIT
  ngOnInit(): void {
    // control create modal close according to the validation
    const modalElement = document.getElementById('addInvoiceModal');
    if (modalElement) {
      this.modal = new Modal(modalElement);
    }

    // control edit modal close according to the validation
    const editModalElement = document.getElementById('editInvoiceModal');
    if (editModalElement) {
      this.editModal = new Modal(editModalElement);
    }

    // initialize search form group
    this.searchFormData = this.fb.group({
      invoiceNumber: '',
      peopleName: '',
      periodEndDate: '',
      payrollStatus: '',
      EmailStatus: '',
    });

    // initialize create form group
    this.invoiceForm = this.fb.group({
      assignmentNumber: [null, [Validators.required]],
      periodEndDate: [null, [Validators.required, this.dateValidator.bind(this)]],
      lineItems: this.fb.array([this.createLineItem()], [Validators.required]),
    });
    // initialize edit form group
    this.editInvoiceForm = this.fb.group({
      assignmentNumber: [null, [Validators.required]],
      periodEndDate: [null, [Validators.required,this.dateValidator.bind(this)]],
      lineItems: this.fb.array(
        [this.createEditLineItem()],
        [Validators.required]
      ),
    });

    // CATCH ASSIGNMENTS UNDER THE COMPANY
    this.invoiceService.getInvAssignments(this.companyId).subscribe(
      // res
      (res: any) => {
        console.log('comp');
        console.log(this.companyId);
        console.log(res);
        this.assignments = res.data;
        console.log(this.assignments);
      },

      //error
      (error) => {
        console.error('Error fetching assignments:', error);
      }
    );
    this.loadInvoices();
  }

  // CREATE A NEW LINE ITEM
  createLineItem(): FormGroup {
    // return line item form group
    return this.fb.group({
      description: [null, [Validators.required]],
      quantity: [null, [Validators.required,this.positiveValidator]],
      unitPrice: [null, [Validators.required]],
    });
  }

  positiveValidator(control: AbstractControl): ValidationErrors | null {
    const value = control.value;
    if (value !== null && value < 0) {
      return { positive: true }; 
    }
    return null;
  }

  // CREATE A NEW EDIT LINE ITEM
  createEditLineItem(): FormGroup {
    // return line item form group
    return this.fb.group({
      description: [null, [Validators.required]],
      quantity: [null, [Validators.required,this.positiveValidator]],
      unitPrice: [null, [Validators.required]],
    });
  }

  // CREATE GETTER FUNCTION
  get lineItems(): FormArray {
    //return
    return this.invoiceForm.get('lineItems') as FormArray;
  }
  // UPDATE GETTER FUNCTION
  get EditLineItems(): FormArray {
    //return
    return this.editInvoiceForm.get('lineItems') as FormArray;
  }

  // ADD A NEW CREATE LINE ITEM TO THE ARRAY
  addLineItem(): void {
    this.lineItems.push(this.createLineItem());
  }

  // ADD A NEW EDIT LINE ITEM TO THE ARRAY
  addEditLineItem(): void {
    this.EditLineItems.push(this.createEditLineItem());
  }

  // REMOVE CREATE LINE ITEM FROM FORM ARRAY
  removeLineItem(index: number): void {
    this.lineItems.removeAt(index);
  }

  // REMOVE EDIT LINE ITEM FROM FORM ARRAY
  removeEditLineItem(index: number): void {
    this.EditLineItems.removeAt(index);
  }

  // FUNCTION TO CHECK VALIDITY AND THROW ERROR
  isInputValid(formcontrolName: string): boolean | undefined {
    const inputControlName = this.invoiceForm.get(formcontrolName);

    // return
    return (
      inputControlName?.invalid &&
      (inputControlName.dirty || inputControlName.touched)
    );
  }
  isEditInputValid(formcontrolName: string): boolean | undefined {
    const inputControlName = this.editInvoiceForm.get(formcontrolName);

    // return
    return (
      inputControlName?.invalid &&
      (inputControlName.dirty || inputControlName.touched)
    );
  }

  dateValidator(control: AbstractControl): ValidationErrors | null {
    const inputDate = new Date(control.value);
    const currentDate = new Date();
    const maxDate = new Date();
    maxDate.setFullYear(maxDate.getFullYear() + 1);

    const minDate = new Date();
    minDate.setFullYear(minDate.getFullYear() - 1);

    // if (isNaN(inputDate.getTime())) {
    //   return { 'invalidDate': true };
    // }

    if (inputDate > maxDate) {
      return { 'futureDateTooFar': true };
    }

    if (inputDate < minDate) {
      return { 'pastDateTooFar': true };
    }

    return null;
  }

  handleDatePickerOpen(): void {
    const payrollBatchDateControl = this.invoiceForm.get('periodEndDate');
    if (payrollBatchDateControl) {
      const currentDate = this.formatDate(new Date());
      payrollBatchDateControl.setValue(currentDate);
    }
  }

  formatDate(date: Date): string {
    const year = date.getFullYear();
    const month = ('0' + (date.getMonth() + 1)).slice(-2);
    const day = ('0' + date.getDate()).slice(-2);
    return `${year}-${month}-${day}`;
  }

  // FUNCTION TO CHECK AND THROW ERROR ACCORDING TO THE LINE ITEM
  isLineItemValid(index: number, formcontrolName: string): boolean | undefined {
    const inputControlName = this.lineItems.at(index).get(formcontrolName);

    //return
    return (
      inputControlName?.invalid &&
      (inputControlName.dirty || inputControlName.touched)
    );
  }

  // FUNCTION TO CHECK AND THROW ERROR ACCORDING TO THE EDIT LINE ITEM
  isEditLineItemValid(
    index: number,
    formcontrolName: string
  ): boolean | undefined {
    const inputControlName = this.EditLineItems.at(index).get(formcontrolName);

    //return
    return (
      inputControlName?.invalid &&
      (inputControlName.dirty || inputControlName.touched)
    );
  }

  // FUNCTION TO RESET CREATE FORM
  resetCreateForm(): void {
    this.invoiceForm.reset();
    this.lineItems.clear();
    //add one line item initially
    this.addLineItem();
  }
  resetEditForm(): void {
    this.editInvoiceForm.reset();
    this.EditLineItems.clear();
    //add one line item initially
    // this.addEditLineItem();
  }

  // FUNCTION TO SUBMIT CREATE FORM
  onCreateSubmit(): void {
    this.isLoading = true;

    //get the values and store it in the variable
    const formData = this.invoiceForm.value;
    console.log(formData);

    this.invoiceService.createInvoice(this.companyId, formData).subscribe(
      // res
      (res: any) => {
        console.log(res);
        this.toastr.success(res.message, 'Success');
        this.isLoading = false;
        this.closeAddInvoiceModal();
        this.loadInvoices();
      },

      //error
      (error: any) => {
        this.isLoading = false;

        this.toastr.error(error.error.error, 'Failed to create');
        console.log(error);
      }
    );
  }

  // FUNCTION TO SUBMIT EDIT FORM
  onEditSubmit(): void {
    this.isLoading = true;
    //get the values and store it in the variable
    const editFormData = this.editInvoiceForm.value;
    console.log(editFormData);
    console.log(this.editInvID);
    this.invoiceService
      .updateInvoice(this.companyId, this.editInvID, editFormData)
      .subscribe(
        // res
        (res: any) => {
          this.toastr.success(res.message, 'Success');
          console.log(res);
          this.isLoading = false;

          this.closeEditInvoiceModal();
          this.loadInvoices();
        },

        //error
        (error: any) => {
          this.isLoading = false;
          this.toastr.error(error.error.error, 'Failed to create');

          console.log(error);
        }
      );
  }

  //FUNCTION IF PAGE CHANGE
  pageChange(event: any) {
    //update kendo page change skip and pageSize
    this.skip = event.skip;
    this.pageSize = event.take;
    this.loadInvoices();
  }

  // FUNCTION TO CHECK SEARCH SUBMIT ATLEAST ONE FIELD IS REQUIRED
  isFormEmpty(): boolean {
    const {
      invoiceNumber,
      peopleName,
      periodEndDate,
      payrollStatus,
      EmailStatus,
    } = this.searchFormData.value;
    return (
      !invoiceNumber &&
      !peopleName &&
      !periodEndDate &&
      !payrollStatus &&
      !EmailStatus
    );
  }

  // FUNCTION TO RESET FORM AND LOAD INVOICES
  onSearchReset(): void {
    this.searchFormData.reset({
      invoiceNumber: '',
      peopleName: '',
      periodEndDate: '',
      payrollStatus: '',
      EmailStatus: '',
    });
    this.loadInvoices();
  }

  // RESET SKIP PAGE TO ZERO AND LOAD INVOICE ACCORDINGLY
  onSearch(): void {
    this.skip = 0;
    this.loadInvoices();
  }

  // FUNCTION TO LOADINVOICES
  loadInvoices(): void {
    this.isGridLoading = true;
    let formData:any;
    if(this.isFormEmpty()){
      formData = null;
    }
    else{
      formData = this.searchFormData.value;
    }
    // subscribe the results
    this.invoiceService
      .getAllInvoices(
        this.companyId,
        this.skip,
        this.pageSize,
        formData
      )
      .subscribe(
        // res
        (res: any) => {
          console.log(res);
          // set the total data and its count
          this.companyName = res.data.company_name;
          this.gridData = {
            data: res.data.invoices_data,
            total: res.data.total_invoice_count,
          };
          this.isGridLoading = false;
        },

        // error
        (error: any) => {
          this.isGridLoading = false;
          console.log(error);
          this.toastr.error(error.error.message, 'Failed');
        }
      );
  }

  isDisabled: { [id: number]: boolean } = {};

  // FUNCTION TO SENDMAIL
  sendMail(invId: number) {
    this.isDisabled[invId] = true; // Disable the button
    this.toastr.info('Sending mail in progress.....');

    // subscribe the sendmail request
    this.invoiceService.sendMail(invId).subscribe(
      // res
      (res: any) => {
        this.loadInvoices();
        this.isGridLoading = false;
        this.toastr.success(res.message, 'Email sent Successfull');
        this.isDisabled[invId] = false; // Disable the button

      },

      // error
      (error: any) => {
        console.log(error);
        const errorMessage = error.error.message;
        this.isGridLoading = false;
        this.toastr.error(errorMessage, 'Failed to send Email');
        this.isDisabled[invId] = false; // Disable the button

      }
    );
  }

   getFormattedTimestamp() {
    const date = new Date();
    const year = date.getFullYear().toString().padStart(4, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0'); 
    const day = date.getDate().toString().padStart(2, '0');
    const hours = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');
    const seconds = date.getSeconds().toString().padStart(2, '0');
  
    return `${year}-${month}-${day}-${hours}-${minutes}-${seconds}`;
  }

  // FUNCTION TO DOWNLOAD MAIL
  downloadMail(invId: number) {
    this.isLoading = true;

    //subscribe download mail request
    this.invoiceService.downloadMail(invId).subscribe(
      // res
      (response: any) => {
        console.log(response);
        this.isLoading = false;
        this.toastr.success('Download Successfull.', 'Success');
        //new blob object of pdf data
        const pdfBlob = new Blob([response], { type: 'application/pdf' });
        //temporary url for blob
        const downloadUrl = window.URL.createObjectURL(pdfBlob);
        //anchor link is created
        const link = document.createElement('a');
        //href is assigned to the url
        link.href = downloadUrl;
        link.download = 'INV0' + invId + '-' + this.getFormattedTimestamp();
        //appends the created link element to the document body (making it invisible).
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      },

      //error
      (error) => {
        this.isLoading = false;
        this.toastr.error('Download Failed.', 'Failed');
        console.error('Failed to download invoice:', error);
      }
    );
  }

  // FUNCTION TO SET DELETE MODAL ITEM
  setDeleteItem(value: any) {
    this.deleteInvId = value;
  }

  // SUBMIT DELETE
  submitDelete(invId: any) {  
    this.isLoading = true;
    //subscribe the delete invoice modal
    this.invoiceService.deleteInvoice(invId).subscribe(
      // res
      (res: any) => {
        console.log(res);
        this.isLoading = false;

        this.toastr.success(res.message, 'Success');
        this.loadInvoices();
      },

      //error
      (error) => {
        this.isLoading = false;
        console.log(error);
        this.toastr.error(error.error.message, 'Failed');
      }
    );
  }

  // FUNCTION TO OPEN INVOICE MODAL
  openAddInvoiceModal(): void {
    this.modal.show();
  }

  // FUNCTION TO CLOSE ADD INVOICE AND HIDE THE MODAL
  closeAddInvoiceModal(): void {
    this.resetCreateForm();
    this.modal.hide();
  }

  // FUNCTION TO CLOSE EDIT INVOICE AND HIDE THE MODAL
  closeEditInvoiceModal(): void {
    this.resetEditForm();
    this.editModal.hide();
  }

  // GET THE EDIT DETAILS
  fetchEditDetails(invId: any) {
    this.isLoading = true;
    this.editInvID = invId;
    this.editModal.show();
    this.invoiceService.getEditDetails(invId).subscribe(
      (res: any) => {
        console.log(res);
        const data = res.data;
        this.editInvoiceForm.patchValue({
          assignmentNumber: data.assignment_num.assignment_num,
          periodEndDate: data.inv_record.period_end_date,
        });

        // clear existing line items
        this.EditLineItems.clear();

        // create new form groups for each line item and add to the form array
        data.invoice_details.forEach((item: any) => {
          const lineItemGroup = this.fb.group({
            description: [item.description, [Validators.required]],
            quantity: [item.quantity, [Validators.required,this.positiveValidator]],
            unitPrice: [item.unit_price, [Validators.required,this.positiveValidator]],
          });
          this.EditLineItems.push(lineItemGroup);
        });
        this.isLoading = false;
      },
      (error: any) => {
        this.isLoading = false;
        console.log(error);
      }
    );
  }
}
