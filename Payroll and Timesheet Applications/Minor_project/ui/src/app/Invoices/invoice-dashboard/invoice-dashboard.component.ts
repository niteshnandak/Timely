import { Component, Input, OnInit } from '@angular/core';
import {
  FormControl,
  FormGroup,
  FormsModule,
  NgModel,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms';
import {
  AddEvent,
  CancelEvent,
  EditEvent,
  GridComponent,
  GridDataResult,
  GridModule,
  PageChangeEvent,
  RemoveEvent,
  SaveEvent,
} from '@progress/kendo-angular-grid';
import { InvoiceService } from '../../services/invoice.service';
import {
  CompositeFilterDescriptor,
  FilterDescriptor,
  GroupDescriptor,
  SortDescriptor,
  State,
  filterBy,
  groupBy,
  orderBy,
} from '@progress/kendo-data-query';
import { CommonModule } from '@angular/common';
import { LoaderComponent } from '../loader/loader.component';
import { HeaderComponent } from '../../header/header.component';
import { HttpResponse } from '@angular/common/http';
import { ToastrService } from 'ngx-toastr';
import { DropDownsModule } from '@progress/kendo-angular-dropdowns';

interface SingleFilter {
  text: string;
  value: boolean;
}

@Component({
  selector: 'app-invoice-dashboard',
  standalone: true,
  imports: [
    CommonModule,
    GridModule,
    LoaderComponent,
    HeaderComponent,
    DropDownsModule,
    FormsModule,
  ],
  templateUrl: './invoice-dashboard.component.html',
  styleUrl: './invoice-dashboard.component.css',
})
export class InvoiceDashboardComponent implements OnInit {
  // Variables
  public invoiceData: any[] = [];
  public filteredData: any = [];
  public groups!: GroupDescriptor[];
  public gridView: any = [];
  public updatedFilters: FilterDescriptor[] = [];
  public take = 10;
  public skip = 0;
  public total = 0;
  isDisabled: { [id: number]: boolean } = {};
  isPageable: boolean = true;
  isLoading: boolean = false;
  gridLoading: boolean = false;

  public lastInvoice = 0;

  public formGroup?: FormGroup;
  private editedRowIndex?: number;

  constructor(
    private invoiceService: InvoiceService,
    private toastService: ToastrService
  ) {}

  ngOnInit() {
    this.loadInvoices(this.skip, this.take);
  }

  //Load Invoices from Server/Back-end
  loadInvoices(skip: number, take: number) {
    this.gridLoading = true;
    this.invoiceService.getAllInvoices(this.skip, this.take).subscribe(
      (result) => {
        this.invoiceData = result.data;
        this.filteredData = result.data;
        this.total = result.total;
        this.loadItems();
        this.gridLoading = false;
      },
      (error) => {
        this.toastService.error(error.error.toaster_error);
      }
    );
  }

  // PageChange
  public pageChangeHandler(event: PageChangeEvent) {
    this.skip = event.skip;
    console.log(this.filter);
    this.loadFilteredData();
  }

  private loadItems() {
    this.gridView = {
      data: this.invoiceData.slice(this.skip, this.skip + this.take),
      total: this.total,
    };
  }

  //FilterChange
  isSingleFilter(filter: any): filter is SingleFilter {
    return typeof filter === 'object' && 'text' in filter && 'value' in filter;
  }

  public filter: CompositeFilterDescriptor = {
    logic: 'and',
    filters: [],
  };

  public filterChange(filter: any) {
    this.skip = 0;

    console.log(filter);

    const newFilter: FilterDescriptor = {
      field: 'generated_status',
      operator: 'eq',
      value: filter.value,
    };

    if (filter && filter.filters && !this.isSingleFilter(filter)) {
      this.updatedFilters = filter.filters;
      this.filter = filter;
    } else if (this.isSingleFilter(filter)) {
      if (
        this.updatedFilters &&
        this.updatedFilters.length > 0 &&
        this.filter.filters.length == 0
      ) {
        this.updatedFilters.forEach((filterItem: FilterDescriptor) => {
          this.filter.filters.push(filterItem);
        });
      } else {
        const generatedStatusIndex = this.filter.filters.findIndex(
          (f) => 'field' in f && f.field === 'generated_status'
        );

        if (generatedStatusIndex === -1) {
          const newFilter: FilterDescriptor = {
            field: 'generated_status',
            operator: filter.value === false ? 'neq' : 'eq',
            value:
              filter.value === true ? 1 : filter.value === false ? 1 : null,
          };
          this.filter.filters.push(newFilter);
        } else {
          if (filter.value === null) {
            this.filter.filters.splice(generatedStatusIndex, 1);
          } else {
            const generatedStatusFilter = this.filter.filters[
              generatedStatusIndex
            ] as FilterDescriptor;
            if (filter.value === true) {
              generatedStatusFilter.value = 1;
              generatedStatusFilter.operator = 'eq';
            } else if (filter.value === false) {
              generatedStatusFilter.value = 1;
              generatedStatusFilter.operator = 'neq';
            }
          }
        }
        console.log('here:', this.filter);
        this.updatedFilters = this.filter.filters as FilterDescriptor[];
        console.log('here, uf:', this.updatedFilters);
      }
    }
    this.loadFilteredData();
  }

  loadFilteredData() {
    if (this.filter.filters.length === 0) {
      this.loadItems();
    } else {
      let finalData = filterBy(this.invoiceData, this.filter);
      this.gridView = {
        data: finalData.slice(this.skip, this.skip + this.take),
        total: finalData.length,
      };
    }
  }

  //Grouping
  groupChange(groups: GroupDescriptor[]) {
    this.groups = groups;
    console.log(this.groups);
    this.loadGroupedItems();
  }

  loadGroupedItems() {
    if (this.groups.length === 0) {
      this.loadItems();
    } else {
      this.gridView = groupBy(this.filteredData, this.groups);
    }
  }

  //Sorting
  public sort: SortDescriptor[] = [
    {
      field: '',
      dir: 'asc',
    },
  ];

  sortChange(sort: SortDescriptor[]) {
    this.sort = sort;
    this.loadSortedItems();
  }

  loadSortedItems() {
    this.gridView = {
      data: orderBy(this.invoiceData, this.sort),
      total: this.total,
    };
  }

  generatePdf(id: any) {
    this.gridLoading = true;
    this.invoiceService.generatePdf(id).subscribe(
      (result) => {
        // console.log(result.message);
        this.toastService.success(result.toaster_success);
        this.loadInvoices(this.skip, this.take);
        this.gridLoading = false;
      },
      (error) => {
        const errorMessage = error.error.toaster_error;
        // console.log(errorMessage);
        this.toastService.error(errorMessage);
        this.gridLoading = false;
      }
    );
  }

  viewPdf(id: any) {
    this.invoiceService.viewPdf(id).subscribe(
      (response: Blob) => {
        if (response.type === 'application/json') {
          //error ones
          // console.log(response);
          this.parseBlob(response);
        } else if (response.type === 'application/pdf') {
          const file = new Blob([response], { type: 'application/pdf' });
          const fileURL = URL.createObjectURL(file);
          window.open(fileURL, '_blank');

          // console.log('Viewed pdf: ' +id);
        } else {
          // console.log('Something went wrong! Could not Load PDF');
          this.toastService.error('Something went wrong! Could not Load PDF');
        }
      },
      (error) => {
        // console.error('PDF NOT FOUND:', error);
      }
    );
  }

  downloadPdf(invoice: any) {
    const id = invoice.id;
    console.log(invoice);
    const filename =
      invoice.timesheet_id +
      '_' +
      invoice.worker_id +
      '_' +
      invoice.organisation +
      '_invoice.pdf';
    this.invoiceService.downloadPdf(id).subscribe(
      (response: Blob) => {
        if (response.type === 'application/pdf') {
          console.log(response);

          const fileURL = URL.createObjectURL(response);
          const a = document.createElement('a');
          a.href = fileURL;
          a.download = filename ? filename : 'invoice.pdf';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);

          this.toastService.success('PDF Downloaded Succesfully '); // + invoice.worker_name
        } else if (response.type === 'application/json') {
          console.log(response);
          this.parseBlob(response);
        } else {
          // console.log('Something went wrong! Could not Download PDF');
          this.toastService.error(
            'Something went wrong! Could not Download PDF'
          );
        }
      },
      (error) => {
        // const errorMessage = error.error.message;
        // this.toastService.error(errorMessage);
        // console.log(errorMessage);
      }
    );
  }

  parseBlob(blob: Blob) {
    const reader = new FileReader();
    reader.onload = (event) => {
      // JSON Data incoming from backend
      const jsonData = JSON.parse(reader.result as string);
      console.log('JSON Data:', jsonData.toaster_error);
      this.toastService.error(jsonData.toaster_error);
    };
    reader.readAsText(blob);
  }

  disableButtonCondition(dataItem: any) {}

  //FUNCTION FOR SENDING MAIL
  sendMail(dataItem: any) {
    //Disabling the mail button instead of loader for parellel work
    this.isDisabled[dataItem.id] = true;
    this.toastService.info('Sending mail in progress.....');
    this.invoiceService.sendMail(dataItem.id).subscribe(
      (response) => {
        this.toastService.success(response.message);
        //set boolean to false to activate mail button
        this.isDisabled[dataItem.id] = false;
      },
      (error) => {
        const errorMessage = error.error.message;
        this.toastService.error(
          `Failed to send Mail to ${dataItem.worker_name}`
        );
        //set boolean to false to activate mail button
        this.isDisabled[dataItem.id] = false;
      }
    );
  }

  // Property to store the ID of the selected item
  selectedItemId!: any;

  deletePdfId(id: any) {
    this.selectedItemId = id;
    console.log(this.selectedItemId);
  }

  //Delete Invoice and PDFs
  public onDeleteInvoice(id: any) {
    this.gridLoading = true;
    this.invoiceService.deleteInvoice(id).subscribe((result) => {
      // console.log(result.toaster_success);
      this.toastService.error(result.toaster_success);
      this.loadInvoices(this.skip, this.take);
    });

    this.invoiceService.deletePdf(id).subscribe(
      (response) => {
        console.log(response.toaster_success);
        // this.toastService.success(response.message);
      },
      (error) => {
        const errorMessage = error.error.toaster_error;
        console.log(errorMessage);
        // this.toastService.error(errorMessage);
      }
    );
  }

  onClick() {
    console.log('clicked');
  }
}
