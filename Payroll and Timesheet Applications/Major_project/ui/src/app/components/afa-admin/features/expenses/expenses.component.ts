import { CommonModule, CurrencyPipe } from '@angular/common';
import { Component } from '@angular/core';
import { AbstractControl, FormArray, FormBuilder, FormControl, FormGroup, ReactiveFormsModule, ValidationErrors, Validators } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { GridModule } from '@progress/kendo-angular-grid';
import { LoaderComponent } from '../../../loader/loader.component';
import { ToastrService } from 'ngx-toastr';
import { CompanyService } from '../../../../services/company.service';
import { ExpensesService } from '../../../../services/expenses.service';
import { ComboBoxModule } from '@progress/kendo-angular-dropdowns';
import { Modal } from 'bootstrap';
import { NumberOnlyDirective } from '../../../../directive/number-only/number-only.directive';
import { Title } from '@angular/platform-browser';
import { DecimalNumberOnlyDirective } from '../../../../directive/decimal-only/decimal-only.directive';
import { decimal10_2Validator } from '../../../../auth/validators/decimal10_2Validator';
import { minValueValidator } from '../../../../auth/validators/min-value.validator';
import { maxYearValidator } from '../../../../validations/date-validator';

@Component({
  selector: 'app-expenses',
  standalone: true,
  imports: [RouterLink, GridModule, ReactiveFormsModule, CommonModule, LoaderComponent, NumberOnlyDirective, DecimalNumberOnlyDirective, ComboBoxModule, CurrencyPipe],
  templateUrl: './expenses.component.html',
  styleUrl: './expenses.component.css'
})
export class ExpensesComponent {

  public isLoading: boolean = false;

  companyId!: any;
  company_name !: string;

  searchFormData: any = null;

  addExpenseForm!: FormGroup;
  editExpenseForm!: FormGroup;
  searchExpenseForm!: FormGroup;

  constructor(
    private expenseService: ExpensesService,
    private companyService: CompanyService,
    private toastr : ToastrService,
    private titleService: Title,
  ) {
    this.titleService.setTitle('Expenses');
  }

  addModal: any;
  editModal: any;
  ngOnInit(): void {
    this.isLoading = true;

    this.companyId = this.companyService.getStoredCompanyId()

    const addExpenseModalElement = document.getElementById('addExpenseModal');
    if (addExpenseModalElement) {
      this.addModal = new Modal(addExpenseModalElement);
    }
    // else {
    //   console.error('Modal element not found');
    // }

    const editExpenseModalElement = document.getElementById('editExpenseModal');
    if (editExpenseModalElement) {
      this.editModal = new Modal(editExpenseModalElement);
    }


    this.loadPeopleNames(this.companyId);
    this.loadExpenseTypes();
    this.initializeFormGroup();
    this.loadExpenses(this.searchFormData);
  }

  // INITTIALIZE ALL FORMS
  initializeFormGroup(){
    // Intialize add expense form
    this.addExpenseForm = new FormGroup({
      people_name: new FormControl('',[Validators.required]),
      // expense_type: new FormControl('',[Validators.required]),
      expense_date: new FormControl('',[Validators.required, this.dateValidator.bind(this)]), // maxYearValidator()
      lineItems: new FormArray([this.createLineItem()], Validators.required)
      // amount: new FormControl('',[Validators.required, decimal10_2Validator(), minValueValidator(0.00)])
    })

    // Intialize Edit expense form
    this.editExpenseForm = new FormGroup({
      people_name: new FormControl('',[Validators.required]),
      expense_type: new FormControl('',[Validators.required]),
      expense_date: new FormControl('',[Validators.required, this.dateValidator.bind(this)]), // maxYearValidator()
      amount: new FormControl('',[Validators.required, decimal10_2Validator(), minValueValidator(0.00)])
    })

    // Initialize Search Expense form
    this.searchExpenseForm = new FormGroup({
      people_name: new FormControl(''),
      expense_type: new FormControl(''),
      expense_date_from: new FormControl(''),
      expense_date_to: new FormControl(''),
      status: new FormControl('')
    },
    { validators: [this.dateRangeValidator] });
  }




  // Custom validator for dateFrom and dateTo
  dateRangeValidator(formGroup: AbstractControl): ValidationErrors | null {
    const dateFrom = formGroup.get('expense_date_from')?.value;
    const dateTo = formGroup.get('expense_date_to')?.value;

    // Check if dateTo matches the pattern
    const datePattern = /^\d{4}-\d{2}-\d{2}$/;
    const isDateToValid = dateTo ? datePattern.test(dateTo) : true;

    if (dateFrom && dateTo && dateTo < dateFrom) {
      formGroup.get('expense_date_to')?.setErrors({ dateRangeInvalid: true });
      return { dateRangeInvalid: true };
    } else if (!isDateToValid) {
      formGroup.get('expense_date_to')?.setErrors({ patternInvalid: true });
      return { patternInvalid: true };
    } else {
      formGroup.get('expense_date_to')?.setErrors(null);
      return null;
    }
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
    const payrollBatchDateControl = this.addExpenseForm.get('expense_date');
    if (payrollBatchDateControl?.errors) {
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


  // clearInvalidDate(event: any) {
  //   const assignmentDateControl = this.addExpenseForm.get('expense_date');

  //   if (assignmentDateControl) {
  //       this.handleInvalidDate(assignmentDateControl, event);
  //   }
  // }

  // handleInvalidDate(control: AbstractControl, event: any) {
  //   if (control && this.isYearInvalid(control.value)) {
  //       const date = new Date(control.value);
  //       date.setFullYear(new Date().getFullYear());
  //       event.target.value = date.toISOString().slice(0, 10);
  //       control.setValue(event.target.value);
  //       control.markAsPristine();
  //   }
  // }

  // isYearInvalid(dateValue: string): boolean {
  //   if (dateValue) {
  //       const selectedYear = new Date(dateValue).getFullYear();
  //       const currentYear = new Date().getFullYear();
  //       return selectedYear > currentYear;
  //   }
  //   return false;
  // }


  // FUNCTION TO CHECK SEARCH SUBMIT ATLEAST ONE FIELD IS REQUIRED
  isFormEmpty(): boolean {
    const {
      people_name,
      expense_type,
      expense_date_from,
      expense_date_to,
      status,
    } = this.searchExpenseForm.value;
    return (
      !people_name &&
      !expense_type &&
      !expense_date_from &&
      !expense_date_to &&
      !status
    );
  }

  // FUNCTION TO RESET FORM AND LOAD EXPENSES
  resetSearchExpenseForm(): void {
    this.searchFormData = null;
    this.isLoading = true;

    this.searchExpenseForm.reset({
      people_name: '',
      expense_type: '',
      expense_date_from: '',
      expense_date_to: '',
      status: '',
    });

    this.resetPagination();
    this.loadExpenses(this.searchFormData);
  }

  // RESET SKIP PAGE TO ZERO AND LOAD INVOICE ACCORDINGLY
  onSearch(): void {
    this.isLoading = true;
    console.log(this.searchExpenseForm.value);
    this.searchFormData = this.searchExpenseForm.value;

    if(this.searchFormData.people_name === undefined) {
      this.searchFormData.people_name = '';
    }

    if(this.searchFormData.expense_type === undefined) {
      this.searchFormData.expense_type = '';
    }

    console.log(this.searchFormData);

    this.resetPagination();
    this.loadExpenses(this.searchFormData);
  }

  // TO RESET PAGE TO 1
  resetPagination() {
    this.skip = 0;
  }



  // GETTER FUNCTIONS FOR ADD EXPENSE FORM
  get peopleName(){
    return this.addExpenseForm.get('people_name')
  }

  get expenseType(){
    return this.addExpenseForm.get('expense_type')
  }

  get expenseDate(){
    return this.addExpenseForm.get('expense_date')
  }

  get amount() {
    return this.addExpenseForm.get('amount')
  }

  get lineItems(): FormArray {
    return this.addExpenseForm.get('lineItems') as FormArray;
  }

  createLineItem(): FormGroup {
    return new FormGroup({
      expense_type: new FormControl('', [Validators.required]),
      amount: new FormControl('', [Validators.required, decimal10_2Validator(), minValueValidator(0.00)])
    });
  }

  addLineItem(): void {
    this.lineItems.push(this.createLineItem());
  }

  removeLineItem(index: number): void {
    this.lineItems.removeAt(index);
  }


  openAddExpenseModal(): void {
    this.addModal.show();
  }

  closeAddExpenseModal(): void {
    this.addModal.hide();
    this.resetAddExpenseForm();
  }


  // LOAD EXPENSE TYPES IN DROPDOWNS
  expenseTypes: any[] = [];
  filteredExpenseTypes: any[] = [];
  loadExpenseTypes() {
    this.expenseService.getExpenseTypes().subscribe(
      (response: any) => {
        this.expenseTypes = response.slice();
        this.filteredExpenseTypes = this.expenseTypes.slice();
        // this.expenseTypes = response.map((item: any) => item.expense_type);
        console.log(this.expenseTypes);
      },
      (error) => {
        console.log(error.error);
      }
    );
  }

  // FILTERATION OF EXPENSE TYPE COMBOBOX
  handleExpenseTypesFilter(value: string): void {
    if (value) {
      this.filteredExpenseTypes = this.expenseTypes.filter(
        (s) => s.expense_type.toLowerCase().indexOf(value.toLowerCase()) !== -1
      );
    } else {
      this.filteredExpenseTypes = this.expenseTypes.slice();
    }
  }

  // LOAD PEOPLE NAMES IN DROPDOWNS OF THAT COMPANY
  peopleNames: any[] = [];
  filteredPeopleNames: any[] = [];
  loadPeopleNames(company_id: any): void {
    this.expenseService.getPeopleName(company_id).subscribe(
      (response: any) => {
        this.peopleNames = response.slice();
        this.filteredPeopleNames = this.peopleNames.slice();
        // this.peopleNames = response.map((item: any) => item.people_name);
        console.log(this.peopleNames);
      },
      (error) => {
        console.log(error.error);
        // console.error('Error fetching people names', error);
      }
    );
  }

  // to handle filteration of PEOPLE NAMES IN COMBOBOX
  handlePeopleNamesFilter(value: string): void {
    if (value) {
      this.filteredPeopleNames = this.peopleNames.filter(
        (s) => s.people_name.toLowerCase().indexOf(value.toLowerCase()) !== -1
      );
    } else {
      this.filteredPeopleNames = this.peopleNames.slice();
    }
  }

  // ADD NEW EXPENSE
  addNewExpenseForm(): void {
    this.isLoading = true;
    this.gridloading = true;

    const formData = this.addExpenseForm.value;

    this.expenseService.addExpense(this.companyId, formData).subscribe(
      (response: any) => {
        console.log(response);
        this.toastr.success(response.message);

        this.closeAddExpenseModal();
        this.resetPagination();
        this.loadExpenses(this.searchFormData);
      },
      (error) => {
        console.log(error.error);
        this.toastr.error(error.error.message);

        this.gridloading = false;
        this.isLoading = false;

      }
    );
  }

  // RESET ADD EXPENSE FORM
  resetAddExpenseForm(): void {
    this.addExpenseForm.reset({
      people_name: '',
      expense_type: '',
      expense_date: null,
      amount: null
    });
    this.lineItems.clear();
    //add one line item initially
    this.addLineItem();
  }


  // GETTER FUNCTIONS FOR EDIT EXPENSE FORM
  get editPeopleName(){
    return this.editExpenseForm.get('people_name')
  }

  get editExpenseType(){
    return this.editExpenseForm.get('expense_type')
  }

  get editExpenseDate(){
    return this.editExpenseForm.get('expense_date')
  }

  get editAmount() {
    return this.editExpenseForm.get('amount')
  }

  // TO FETCH THE EDIT EXPENSE FORM DATA
  getEditExpenseData(expense_id: any) {
    this.expenseId = expense_id;
    this.expenseService.getEditExpenseData(expense_id).subscribe(
      (response: any) => {
        console.log(response);

        const formData = {
          people_name: response.people_id,
          expense_type: response.expense_type_id,
          expense_date: response.expense_date,
          amount: response.amount,
        };

        this.editExpenseForm.patchValue(formData);

        this.openEditExpenseModal();
      },
      (error) => {
        console.log(error.error);
      }
    );
  }

  openEditExpenseModal(): void {
    this.editModal.show();
  }

  closeEditExpenseModal(): void {
    this.editModal.hide();
  }

  // TP UPDATE THE EXPENSE DATA
  updateExpenseForm(expenseId: any) {

    this.isLoading = true;
    this.gridloading = true;

    const formData = this.editExpenseForm.value;

    this.expenseService.updateExpenseData(expenseId, formData).subscribe(
      (response: any) => {
        console.log(response);

        this.closeEditExpenseModal();

        this.toastr.success(response.message);

        this.loadExpenses(this.searchFormData);
      },
      (error) => {
        console.log(error.error);
        this.toastr.error(error.error.message);

        this.gridloading = false;
        this.isLoading = false;
      }
    );

  }

  // RESET EDIT EXPENSE FORM
  resetEditExpenseForm(): void {
    this.editExpenseForm.reset({
      people_name: '',
      expense_type: '',
      expense_date: null,
      amount: null
    });
  }




  // KENDO GRID VARIABLES
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
    this.loadExpenses(this.searchFormData);
  }



  // TO LOAD EXPENSE FORM WITH SEARCHED DATA IF ANY
  loadExpenses(searchFormData: any): void {
    this.gridloading = true;

    this.expenseService.getExpenses(this.companyId, this.skip, this.pageSize, searchFormData).subscribe(
      (response: any) => {
        // console.log(response);
        // console.log(response.searchedUserData);
        // console.log(response.total);

        this.company_name = response.companyName;

        if (response.searchedUserData && response.searchedUserData.length > 0) {
          this.gridData = {
            data: response.searchedUserData,
            total: response.total
          };
        } else {
          if (this.skip > 0) {
            this.skip = this.skip - 10;
            this.loadExpenses(searchFormData); // Re-fetch the data with the updated skip value
            return;
          } else {
            this.gridData = {
              data: [],
              total: 0
            };
            this.toastr.error(response.message);
          }
        }

        console.log(this.gridData);

        this.gridloading = false;
        this.isLoading = false;
      },
      (error) => {
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



  // FUNCTION TO GET expense_id OF CURRENT LINE ITEM
  expenseId:any;
  setDataItem(expense_id: any) {
    this.expenseId = expense_id;
  }

  // FUNCTION TO DELETE EXPENSE
  deleteExpense(expense_id: any) {
    this.gridloading = true;

    this.expenseService.deleteExpense(this.companyId, expense_id).subscribe(
      (response: any) => {
        this.toastr.success(response.message);
        this.loadExpenses(this.searchFormData);
      },
      (error) => {
        console.log(error.error);
        this.toastr.error(error.error.message);

        this.gridloading = false;
      }
    );
  }

  // FUNCTION TO APPROVE THE EXPENSE
  approveExpense(expense_id: any) {
    this.gridloading = true;

    this.expenseService.approveExpense(this.companyId, expense_id).subscribe(
      (response: any) => {
        this.toastr.success(response.message);
        this.loadExpenses(this.searchFormData);

      },
      (error) => {
        console.log(error.error);
        this.toastr.error(error.error.message);

        this.gridloading = false;
      }
    );
  }

}
