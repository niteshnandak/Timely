import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { CompanyService } from '../../../services/company.service';
import { CommonModule } from '@angular/common';
import { AssignmentService } from '../../../services/assignment.service';
import { CustomersService } from '../../../services/customers.service';
import { LoaderComponent } from '../../loader/loader.component';
import { Title } from '@angular/platform-browser';

@Component({
  selector: 'app-company-detail',
  standalone: true,
  imports: [CommonModule, LoaderComponent, RouterLink],
  templateUrl: './company-detail.component.html',
})
export class CompanyDetialComponent {
  company: any;
  error: string | null = null;
  companyId: string | null = null;
  company_logo_path:string = "";
  assignment_completed_count:any;
  assignment_ongoing_count:any;
  assignment_total_count:any;
  assignment_week_count:any;
  assignment_month_count:any;
  customers_active_count:any;
  customers_inactive_count:any;
  customer_total_count:any;
  customer_week_count:any;
  customer_month_count:any;
  gridLoading !: boolean;
  public isLoading: boolean = true

  constructor(
    private route: ActivatedRoute, 
    private companyService: CompanyService, 
    private router: Router,
    private assignmentService: AssignmentService,
    private customerService: CustomersService,
    private title: Title
  ){
    this.title.setTitle('Company Details')
  }

  // ngOnInit(): void {
  //   this.companyId = this.companyService.getStoredCompanyId();
  //   if (this.companyId) {
  //     this.companyService.getCompanyId(this.companyId).subscribe(
  //       response => {
  //         this.company = response;
  //         this.company_logo_path = "http://127.0.0.1:8000/api/org-logo/"+this.company['company_logo_path'];
  //         console.log('Company details fetched successfully', this.company);
          
  //       },
  //       error => {
  //         console.error('Error fetching company details', error);
  //       }
        
  //     );
  //   } else {
  //     console.error('No company ID found in session storage');
  //   }
  //   this.fetchAssignmentStats();
  //   this.fetchCustomerStats();
    
  // }

  ngOnInit(): void {
    this.companyId = this.companyService.getStoredCompanyId();
    if (this.companyId) {
      this.companyService.getCompanyId(this.companyId).subscribe(
        response => {
          this.company = response;
          this.company_logo_path = `http://127.0.0.1:8000/api/org-logo/${this.company['company_logo_path']}`;
          console.log('Company details fetched successfully', this.company);
        },
        error => {
          console.error('Error fetching company details', error);
        }
      );
    } else {
      console.error('No company ID found in session storage');
    }
    this.fetchAssignmentStats();
    this.fetchCustomerStats();
  }

  fetchAssignmentStats(): void {
    // Replace 'companyId' with the actual company ID
    const companyId = 'companyId';
    this.assignmentService.getAssignmentStats(this.companyId).subscribe((result:any)=>{
      console.log(result);
    
      this.assignment_completed_count = result.assignment_completed;
      this.assignment_ongoing_count = result.assignment_ongoing;
      this.assignment_total_count = result.assignment_total;
      this.assignment_week_count = result.assignment_last_week;
      this.assignment_month_count = result.assignment_last_month;

    });
  }

  fetchCustomerStats(): void {
    // Replace 'companyId' with the actual company ID
    const companyId = 'companyId';
    this.customerService.fetchCustomerStats(this.companyId).subscribe((result:any)=>{



      this.customers_active_count = result.customer_active;
      this.customers_inactive_count = result.customer_inactive;
      this.customer_total_count = result.customer_total;
      this.customer_week_count = result.customer_last_week;
      this.customer_month_count = result.customer_last_month;

      this.isLoading = false;
    });
  }


  // ngOnDestroy(): void {
  //   this.companyService.removeCompanyId();
  // }
}
