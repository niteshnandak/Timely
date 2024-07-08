import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { GridModule } from '@progress/kendo-angular-grid';
import { LoaderComponent } from '../../../../loader/loader.component';
import { RouterLink, Router, ActivatedRoute } from '@angular/router';
import { InvoiceService } from '../../../../../services/invoice.service';
import { Title } from '@angular/platform-browser';
import { ToastrService } from 'ngx-toastr';
import {
  ReactiveFormsModule,
  FormControl,
  FormGroup,
  FormBuilder,
  Validators,
} from '@angular/forms';
import { empty, isEmpty } from 'rxjs';

@Component({
  selector: 'app-people-invoice',
  standalone: true,
  imports: [
    GridModule,
    CommonModule,
    LoaderComponent,
    RouterLink,
    ReactiveFormsModule,
  ],
  providers: [InvoiceService],
  templateUrl: './people-invoice.component.html',
  styleUrls: ['./people-invoice.component.css'],
})

//CLASS PeopleInvoiceComponent
export class PeopleInvoiceComponent {
  public isLoading!: boolean;
  isGridLoading = false;
  pageSize = 10;
  skip = 0;
  total = 0;
  public gridData: any = { data: [], total: 0 };
  searchFormData!: FormGroup;
  peopleName!: any;

  //CONSTRUCTOR
  constructor(
    private router: Router,
    private fb: FormBuilder,
    private toastr: ToastrService,
    private route: ActivatedRoute,
    private titleService: Title,
    private invoiceService: InvoiceService
  ) {
    this.peopleId = this.route.snapshot.params['id'];
    this.peopleName = this.route.snapshot.params['peopleName'];

    // Initialize the search form
    this.searchFormData = this.fb.group({
      invoiceNumber: [null],
      assignmentNumber: [null],
      customerName: [null],
      periodEndDate: [null],
      emailStatus: [''],
      payrollStatus: [''],
    });

    this.loadInvoices();
  }

  peopleId!: any;

  //NGONINIT
  ngOnInit() {}

  // FUNCTION TO SEARCH AND LOAD INVOICES
  onSearch() {
    this.skip = 0;
    this.loadInvoices();
  }

  // FUNCTION TO RESET AND LOAD INVOICES
  onSearchReset(): void {
    this.searchFormData.reset(
      {
        invoiceNumber: null,
        assignmentNumber: null,
        customerName: null,
        periodEndDate: null,
        emailStatus: '',
        payrollStatus: '',
      }
    );
    this.loadInvoices();
  }

  // FUNCTION TO CHECK SEARCH FORM IS EMPTY OR NOT
  isFormEmpty(): boolean {
    return (
      !this.searchFormData.get('invoiceNumber')?.value &&
      !this.searchFormData.get('assignmentNumber')?.value &&
      !this.searchFormData.get('customerName')?.value &&
      !this.searchFormData.get('periodEndDate')?.value &&
      !this.searchFormData.get('emailStatus')?.value &&
      !this.searchFormData.get('payrollStatus')?.value
    );
  }

  // FUNCTION TO PAGECHANGE
  pageChange(event: any) {
    //update kendo page change skip and pageSize
    this.skip = event.skip;
    this.pageSize = event.take;
    this.loadInvoices();
  }

  //FUNCTION TO LOADINVOICES
  loadInvoices(): void {
    console.log(this.searchFormData.value);
    this.isGridLoading = true;

    // checking id the data is there or not an sending the datas
    let formData:any;
    if(this.isFormEmpty()){
      formData = null;
    }
    else{
      formData = this.searchFormData.value;
    }

    this.invoiceService
      .loadInvoices(
        this.peopleId,
        this.skip,
        this.pageSize,
        formData
      )
      .subscribe(
        // res
        (res) => {
          console.log('i am at res', res);
          this.gridData = {
            data: res["data"]["invoice_data"],
            total: res["data"]["total"],
          };
          this.isGridLoading = false;
        },

        // error
        (error) => {
          this.isGridLoading = false;
          console.log(error);
        }
      );
  }
}
