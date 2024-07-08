import { Component } from '@angular/core';
import { GridModule } from '@progress/kendo-angular-grid';
import { CommonModule } from '@angular/common';
import {
  trigger,
  state,
  style,
  animate,
  transition
 } from '@angular/animations';
import { ActivatedRoute, Router, RouterOutlet } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { AssignmentService } from '../../../../../services/assignment.service';
import { CompanyService } from '../../../../../services/company.service';
import { CustomersService } from '../../../../../services/customers.service';
import { ToastrService } from 'ngx-toastr';
import { LoaderComponent } from '../../../../loader/loader.component';
import { Title } from '@angular/platform-browser';

@Component({
  selector: 'app-assignment-dashboard',
  standalone: true,
  imports: [GridModule, CommonModule, RouterOutlet ,GridModule, LoaderComponent],
  templateUrl: './assignment-dashboard.component.html',
  styleUrl: './assignment-dashboard.component.css',

  animations: [
    trigger('popOverState', [
      state('open', style({
        height: '80vh',
      })),
      transition('* => *', animate('500ms ease')),
    ]),
    trigger('heightControl', [
      state('open', style({
        height: 0,
        opacity: 0,
        display: 'none',
      })),
      transition('* => *', animate('500ms ease')),
    ]),
    trigger('displayControl', [
      state('open', style({
        display: 'none',
      })),
      transition('* => *', animate('100ms ease')),
    ]),
  ]
})
export class AssignmentDashboardComponent {
  state: 'none' | 'search' | 'add' | 'edit' = 'none';

  public assignmentGridData: any = {
    data : [],
    total: 0
  }
  skip = 0;
  take = 10;
  pageable_status:boolean = true;
  company_id:any;
  assignment_completed_count:any;
  assignment_ongoing_count:any;
  assignment_total_count:any;
  assignment_week_count:any;
  assignment_month_count:any;
  gridLoading !: boolean;
  isLoading !: boolean;
  deleteAssignmentId : any;
  company_name !: string;
  // currentEditAssignmentId: number | null = null;

  constructor(
    private assignmentService : AssignmentService,
    private http:HttpClient, private router:Router,
    private route:ActivatedRoute,
    private companyService: CompanyService,
    private customerService: CustomersService,
    private toastr: ToastrService,
    private titleService: Title
  ) {
    this.titleService.setTitle('Assignment');
  }

  ngOnInit(){
   this.isLoading = true;
   this.loadAssignments();
   this.getAssignmentStats();
   this.company_id = this.route.snapshot.params['id'];
   if(this.state === 'none'){
    this.router.navigateByUrl('/company/company-details/assignments')
    }

    this.customerService.searchData.subscribe((result:any)=>{
      console.log(result);
      this.skip = 0;
      this.take = 10;
      this.assignmentGridData = {
        data: result.result,
        total: result.total
      }
    })

    this.customerService.searchEvent.subscribe(()=>{
      this.state = 'none';
      this.loadAssignments();
    })

    this.assignmentService.assignmentSearchClickEvent.subscribe((data)=>{
      if(data === true){
        this.loadAssignments();
      }
    })
  }

  loadAssignments(): void {
    this.company_id = this.companyService.getStoredCompanyId();
    this.gridLoading = true;
    this.assignmentService.getAllAssignments(this.skip, this.take, this.company_id).subscribe((result:any) => {

      console.log("assgn",result);

      this.assignmentGridData = {
        data: result.assignments,
        total: result.total
      }
      this.gridLoading = false;

    });
  }

  getAssignmentStats(){
    this.assignmentService.getAssignmentStats(this.company_id).subscribe((result:any)=>{
      console.log(result);

      this.company_name = result.company_name;
      this.assignment_completed_count = result.assignment_completed;
      this.assignment_ongoing_count = result.assignment_ongoing;
      this.assignment_total_count = result.assignment_total;
      this.assignment_week_count = result.assignment_last_week;
      this.assignment_month_count = result.assignment_last_month;

      this.isLoading = false;
    });
  }

  assignmentPageChange(result:any){
    this.skip = result.skip;
    this.take = result.take;
    this.loadAssignments();
  }

  setDataItem(value:any){
    this.deleteAssignmentId = value;
  }

  deleteAssignmentData(assignment_id: Number) {
    console.log(assignment_id);
    this.isLoading = true;
    this.assignmentService.deleteAssignment(assignment_id).subscribe({
      next:(result:any)=>{
      console.log(result);
      this.loadAssignments();
      this.isLoading = false;
      this.toastr.success(result["message"]);
    },
    error:(result:any)=>{
      this.toastr.error(result["message"]);
    }

  });
  }

  toggleState(value: 'none' | 'search' | 'add' | 'edit',assignment_id:any) {
    this.state = this.state === value && value != 'edit' ? 'none' : value;

    if(this.state === 'search'){
      setTimeout(() => {
        this.router.navigateByUrl('company/company-details/assignments/search-assignment')
      }, 500);

    }else if(this.state === 'add'){
      setTimeout(() => {
        this.loadAssignments();
        this.router.navigateByUrl('company/company-details/assignments/add-assignment')
      }, 500);

    }else if(this.state === 'none'){
      this.router.navigateByUrl('/company/company-details/assignments')
      this.loadAssignments();

    }else if(this.state === 'edit'){
      // this.currentEditAssignmentId = assignment_id;
      this.router.navigateByUrl('/company/company-details/assignments/edit-assignment/'+assignment_id)  ;
      this.state = 'edit'
    }
  }
}
