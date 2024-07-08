import { Component, EventEmitter, Output, output } from '@angular/core';
import { ActivatedRoute, NavigationExtras, Router } from '@angular/router';
import { GridModule } from '@progress/kendo-angular-grid';
import { GridDataResult } from '@progress/kendo-angular-grid';
import { CompositeFilterDescriptor, GroupDescriptor, GroupResult, SortDescriptor, filterBy, orderBy, process } from '@progress/kendo-data-query';
import { groupBy } from '@progress/kendo-data-query';
import { and } from '@progress/kendo-angular-grid/utils';
import { filter } from '@progress/kendo-data-query/dist/npm/transducers';
import { CompanyService } from '../../../../services/company.service';
import { gridData } from '../../../../models/griddata';
import { CommonModule } from '@angular/common';
import { CustomersService } from '../../../../services/customers.service';
import { AuthService } from '../../../../auth/auth-services/auth.service';
import { Title } from '@angular/platform-browser';
import { ToastrService } from 'ngx-toastr';
import { LoaderComponent } from '../../../loader/loader.component';

@Component({
  selector: 'app-company-kendo-grid',
  standalone: true,
  imports: [
    GridModule,
    CommonModule,
    LoaderComponent
  ],
  templateUrl: './company-kendo-grid.component.html'
})
export class CompanyKendoGridComponent {
  @Output() statusUpdated = new EventEmitter<void>
  companies: any[] = [];
  gridData: any = { data: [], total: 0 };
  gridloading = false;
  pageSize = 10;
  skip = 0;
  pageableFlag:boolean = true;
  org_id:any;
  total = 0;
  public isLoading !: boolean;

  constructor(private route: ActivatedRoute, private router: Router,
              private companyService: CompanyService,
              private customerService:CustomersService,
              private authService:AuthService,
              private title: Title,
              private toast: ToastrService
            ) {
              this.title.setTitle('Company')
            }

  ngOnInit() {

    this.org_id = this.authService.getUser()['organisation_id'];
    this.customerService.searchEvent.subscribe(()=>{
      this.loadItem();
    })


    this.customerService.searchData.subscribe((result:any)=>{
      console.log(result['result']);
      this.skip= 0;
      this.gridData = {
        data: result['result'],
        total: this.gridData.total
      }
    })

    this.loadItem();

  }

  pageChange(event: any) {
    this.skip = event.skip;
    this.pageSize = event.take;
    this.loadItem();
  }

  loadItem() {
    // this.gridloading = true;
    this.isLoading = true
    this.companyService.fetchCompanyData(this.skip, this.pageSize, this.org_id).subscribe((response) => {
      this.companies = response.companies;
      this.total = response.total;

      this.gridData = {
        data: this.companies,
        total: this.total
      };
      // this.gridloading = false;
      this.isLoading = false;
    });
  }

  navigateToCompanyDetails(companyId: string): void {
    // const navigationExtras: NavigationExtras = { state: { skip: this.skip } };
    console.log('Storing company ID in session storage:', companyId);
    this.companyService.storeCompanyId(companyId);
    this.router.navigate(['/company/company-details']);
  }

  deleteCompany(companyId: string): void {
    //  this.gridloading = true;
      this.companyService.deleteCompany(companyId).subscribe(
        response => {
          console.log('Company deleted successfully');
          this.loadItem(); // Refresh the grid data
          this.toast.success(response['toaster_success']);
          // this.gridloading = false;
          this.statusUpdated.emit();

        },
        error => {
          console.error('Error deleting company', error);
        }
      );
    
  }
  deleteCompanyId : any;
 
  setDataItem(value:any){
    this.deleteCompanyId = value;
  }
  toggleStatus(company: any): void {
    this.gridloading=true
    const newStatus = company.status === 1 ? 0 : 1;
    this.companyService.updateCompanyStatus(company.company_id, newStatus).subscribe(response => {
      company.status = newStatus;
      this.statusUpdated.emit();

      if(newStatus == 1){
        this.toast.success(response['toaster_success']);
        this.gridloading=false;
      }
      else{
        this.toast.success(response['toaster_success1']);
      this.gridloading=false;
      }
      
    }, error => {
      console.error('Error updating status', error);
      this.toast.success('Error occured')
    });
    
  }

  navigateToCompanySettings(companyId: string) {
    console.log('Storing company ID in session storage:', companyId);
    this.companyService.storeCompanyId(companyId);
    this.router.navigate(['/company/company-settings'], { queryParams: { id: companyId } });
  }
}
