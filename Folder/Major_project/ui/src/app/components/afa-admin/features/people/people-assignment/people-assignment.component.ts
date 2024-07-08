import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { GridModule } from '@progress/kendo-angular-grid';
import { LoaderComponent } from '../../../../loader/loader.component';
import { RouterLink, Router, ActivatedRoute } from '@angular/router';
import { AssignmentService } from '../../../../../services/assignment.service';
import { Title } from '@angular/platform-browser';
import { ToastrService } from 'ngx-toastr';
import { ReactiveFormsModule, FormControl, FormGroup, FormBuilder, Validators } from '@angular/forms';

@Component({
  selector: 'app-people-assignment',
  standalone: true,
  imports: [GridModule, CommonModule, LoaderComponent, RouterLink, ReactiveFormsModule],
  providers: [AssignmentService],
  templateUrl: './people-assignment.component.html',
  styleUrl: './people-assignment.component.css',
})
export class PeopleAssignmentComponent {
  public isLoading!: boolean;
  isGridLoading = false;
  pageSize = 10;
  skip = 0;
  total = 0;
  public gridData: any = { data: [], total: 0 };
  searchFormData: any = {};
  peopleName!: any;

  constructor(
    private router: Router,
    private fb: FormBuilder,
    private toastr: ToastrService,
    private route: ActivatedRoute,
    private titleService: Title,
    private assignmentService: AssignmentService
  ) {
    this.peopleId = this.route.snapshot.params['id'];
    this.peopleName = this.route.snapshot.params['peopleName'];
  

    // Initialize the search form
    this.searchFormData = this.fb.group({
      assignmentNumber: [''],
      customerName: [''],
      location: [''],
      status: ['']
    });

    this.loadAssignments();
  }

  peopleId!: any;

  ngOnInit() {
  }

  onSearch(){
    this.skip = 0;
    this.loadAssignments();

  }

  onSearchReset(): void {
    this.searchFormData.reset({
      assignmentNumber: '',
      customerName: '',
      location: '',
      status: ''
    });
    this.loadAssignments();
  }

  isFormEmpty(): boolean {
    return!this.searchFormData.get('assignmentNumber')?.value &&
     !this.searchFormData.get('customerName')?.value &&
     !this.searchFormData.get('location')?.value &&
     !this.searchFormData.get('status')?.value;
  }

  pageChange(event: any) {
    //update kendo page change skip and pageSize
    this.skip = event.skip;
    this.pageSize = event.take;
    this.loadAssignments();
  }

  loadAssignments(): void {
    console.log(this.searchFormData.value);
    this.isGridLoading = true;
    this.assignmentService.loadAssignments(this.peopleId,
      this.skip,
      this.pageSize,
      this.searchFormData.value).subscribe(
      (res) => {
        console.log(res);
        this.gridData = {
          data: res.data[0],
          total: res.data[1]
        };
        this.isGridLoading = false;
        console.log(this.gridData);
      },
      (error) => {
        this.isGridLoading = false;
        console.log(error);
      }
    );
  }
}