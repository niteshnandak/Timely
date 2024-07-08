import { Component } from '@angular/core';
import { LoaderComponent } from '../../../../loader/loader.component';
import { CommonModule, CurrencyPipe } from '@angular/common';
import { GridModule } from '@progress/kendo-angular-grid';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { ComboBoxModule } from '@progress/kendo-angular-dropdowns';
import { ExpensesService } from '../../../../../services/expenses.service';
import { ToastrService } from 'ngx-toastr';
import { Title } from '@angular/platform-browser';
import { AbstractControl, FormControl, FormGroup, ReactiveFormsModule, ValidationErrors } from '@angular/forms';

@Component({
  selector: 'app-people-expenses',
  standalone: true,
  imports: [LoaderComponent, CommonModule, GridModule, RouterLink, ComboBoxModule, ReactiveFormsModule, CurrencyPipe],
  templateUrl: './people-expenses.component.html',
  styleUrl: './people-expenses.component.css'
})
export class PeopleExpensesComponent {

  public isLoading: boolean = false;

  searchFormData: any = null;
  searchExpenseForm!: FormGroup;

  peopleId: any;
  people_name: any;

  constructor(
    private expenseService: ExpensesService,
    private toastr : ToastrService,
    private route: ActivatedRoute,
    private titleService: Title,
  ) {
    this.titleService.setTitle('Expenses');
  }

  ngOnInit(): void {

    this.isLoading = true;

    this.peopleId = this.route.snapshot.params['id'];
    // this.adminAfaService.setOrgId(this.orgId);

    console.log(this.peopleId);

    this.getPersonName(this.peopleId);
    this.loadExpenseTypes();
    this.initializeFormGroup();
    this.loadPeopleExpenses(this.searchFormData);
  }

  // Get People Name for heading
  getPersonName(peopleId: any) {
    this.expenseService.getExpensePersonName(peopleId).subscribe(
      (response: any) => {
        console.log(response);

        this.people_name = response.peopleName;
      },
      (error) => {
        console.log(error.error);
      }
    );
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


  initializeFormGroup(){
    // Initialize Search Expense form
    this.searchExpenseForm = new FormGroup({
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

  // FUNCTION TO CHECK SEARCH SUBMIT ATLEAST ONE FIELD IS REQUIRED
  isFormEmpty(): boolean {
    const {
      expense_type,
      expense_date_from,
      expense_date_to,
      status,
    } = this.searchExpenseForm.value;
    return (
      !expense_type &&
      !expense_date_from &&
      !expense_date_to &&
      !status
    );
  }

  // FUNCTION TO RESET FORM AND LOAD EXPENSES
  resetSearchExpenseForm(): void {
    this.isLoading = true;
    this.searchFormData = null;

    this.searchExpenseForm.reset({
      expense_type: '',
      expense_date_from: '',
      expense_date_to: '',
      status: '',
    });

    this.resetPagination();
    this.loadPeopleExpenses(this.searchFormData);
  }

  // RESET SKIP PAGE TO ZERO AND LOAD INVOICE ACCORDINGLY
  onSearch(): void {
    this.isLoading = true;
    console.log(this.searchExpenseForm.value);
    this.searchFormData = this.searchExpenseForm.value;

    if(this.searchFormData.expense_type === undefined) {
      this.searchFormData.expense_type = '';
    }

    console.log(this.searchFormData);

    this.resetPagination();
    this.loadPeopleExpenses(this.searchFormData);
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
    this.loadPeopleExpenses(this.searchFormData);
  }

  // Load the Kendo grid data
  loadPeopleExpenses(searchFormData: any) {
    this.gridloading = true;

    this.expenseService.getPeopleExpenses(this.peopleId, this.skip, this.pageSize, searchFormData).subscribe(
      (response: any) => {
        console.log(this.peopleId);
        console.log(response);

        this.gridData = {
          data: response.searchedUserData,
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

}
