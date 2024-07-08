import { Component, ElementRef, EventEmitter } from '@angular/core';
import { GridModule } from '@progress/kendo-angular-grid';
import { SideNavbarComponent } from '../../../../side-navbar/side-navbar.component';
import { CommonModule } from '@angular/common';
import {
  trigger,
  state,
  style,
  animate,
  transition
} from '@angular/animations';
import { transform } from '@progress/kendo-drawing/dist/npm/geometry';
import { ActivatedRoute, Router, RouterOutlet } from '@angular/router';
import { PeopleService } from '../../../../../services/people.service';
import { HttpClient } from '@angular/common/http';
import { CustomersService } from '../../../../../services/customers.service';
import { ToastrService } from 'ngx-toastr';
import { AuthService } from '../../../../../auth/auth-services/auth.service';
import { Subscription, take } from 'rxjs';
import { Element } from '@progress/kendo-drawing';
import { LoaderComponent } from '../../../../loader/loader.component';
import { Title } from '@angular/platform-browser';
import { SearchPeopleComponent } from '../search-people/search-people.component';
import { PopoverModule } from '@progress/kendo-angular-tooltip';


@Component({
  selector: 'app-people-dashboard',
  standalone: true,
  imports: [
    GridModule,
    SideNavbarComponent,
    CommonModule,
    RouterOutlet,
    LoaderComponent,
    SearchPeopleComponent,
    PopoverModule
  ],
  templateUrl: './people-dashboard.component.html',




})
export class PeopleDashboardComponent {

  state: 'none' | 'search' | 'add' | 'edit' = 'none';

  gridData: any = { data: [], total: 0 };
  gridloading = false;
  pageSize = 10;
  skip = 0;
  total = 0;
  ngZone: any;
  deletePeopleId:any;
  isLoading !: boolean;
  statusData:any;
  user:any;
  togglePeopleStatus: any;
  dataItem: any;

  showCardState:boolean = false;
  searchCloseState:boolean = false;

  searchData : any = {};
  $peopleSearchSubscription !: Subscription;

  $totalPeople:any;
  $totalPeopleMonthAgo:any;
  $totalPeopleYearAgo:any;
  $peopleWithAssignment:any;
  $peopleWithMapped:any;

  searchShowState :boolean =true;


  expandGrid=true;
  reduceGrid =false;
  flag = true;

  constructor(
    private peopleService: PeopleService,
    private router: Router,
    private route: ActivatedRoute,
    private customerService: CustomersService,
    private toastr:ToastrService,
    private auth:AuthService,
    private elementref:ElementRef,
    private titleService: Title
  ) {
      titleService.setTitle("People");
      route.params.subscribe(val => {
       this.searchData={};
      });

  }
  ngOnInit() {

    this.isLoading = true;
    this.user = this.auth.getUser();
    this.toggleState('none',null)

    this.getStatusData();

    // this.loadPeople();


   this.peopleService.peopleSearchClickEvent.subscribe((reset)=>{

    if(reset === true){
      this.toggleState('none',null);
    }
  })

   this.peopleService.PeopleEditCloseChangeEvent.subscribe((close)=>{
    this.searchData ={};
    if(close === true){
      this.toggleState('none',null);

    }
  })

    this.$peopleSearchSubscription = this.peopleService.peopleData.subscribe((result: any) => {
      if(result) {
          this.searchData = result
          this.searchCloseState = true;
          this.skip = 0;
          this.loadPeople();
          this.searchData ={};
      }
    this.searchData ={};

    })



  }


  loadPeople(): void {


    this.gridloading = true;


    this.peopleService.getAllPeople(this.skip, this.pageSize,this.user, this.searchData).subscribe(data => {

      this.gridData = {
        data: data.people_data,
        total: data.total
      }
      this.gridloading = false;
       this.isLoading = false;


    });
  }



  pageChange(event: any) {
    this.skip = event.skip;
    this.pageSize = event.take;
    this.loadPeople();
  }



  deletePeopleData(people_id: Number) {
    // this.isLoading=true;
    this.gridloading = true;
    this.peopleService.deletePeople(people_id).subscribe({
      next: (response:any)=>{
        this.toastr.success(response['toaster_success'],'Success');
      },

      error: (msg)=>{
        this.toastr.error(msg["toaster_error"], 'Error');
      }
    })
    this.gridloading = false;
    this.loadPeople();
  }

  getStatusData(){
    this.peopleService.cardPeopleData(this.user).subscribe({
      next: (response)=>{
       this.statusData = response.status_data;
       this.$totalPeople = this.statusData['totalPeople'];
       this.$totalPeopleMonthAgo = this.statusData['newPeopleLastWeek'];
       this.$totalPeopleYearAgo = this.statusData['newPeopleLastMonth'];
       this.$peopleWithAssignment = this.statusData['peopleAssignmentMapped'];
       this.$peopleWithMapped = this.statusData['peopleCompanyMapped']

      }
    })
  }

  setDataItem(value:any){
    this.deletePeopleId = value;
  }

  toggleShowCard(){
    this.showCardState = !this.showCardState;
    // if(this.showCardState){
    //   this.elementref.nativeElement.getElementById('toggleShowCard').style.display ="block";
    //   // document.getElementById("toggleShowCard").style.display = "block";
    // }
  }

  expand() {
    if(this.flag){
    this.expandGrid = false;
    this.reduceGrid = true;
    this.flag = false
    }else{
      this.expandGrid = true;
      this.reduceGrid = false;
      this.flag=true;
    }

 }


  dataItemChange(dataItem:any){
    if(dataItem){
      this.dataItem = dataItem;
    }
  }

  getDataItem(){
    return this.dataItem;
  }

  toggleStatus(people: any): void {
    // this.isLoading=true;
    this.gridloading = true;
    if(people){
      const newStatus = people.status == 1 ? 0 : 1;

      this.peopleService.updatePeopleStatus(people.people_id, newStatus).subscribe(response => {

        people.status = newStatus;
        this.toastr.success("People Status Updated Successfully");
        // this.statusUpdated.emit();
        this.loadPeople()
      }, error => {
        console.error('Error updating status', error);
        this.toastr.error(error.error.message);

      });
    }
  }



  setToggleDataItem(event: any, data: any, status: any) {
    event.preventDefault();
    this.togglePeopleStatus = data;
    this.toggleStatus = status;
  }

  toggleState(value: 'none' | 'search' | 'add' | 'edit', people_id: any) {

    this.state = this.state === value && value != 'edit' ? 'none' : value;

   if (this.state === 'add') {
    this.searchData = {};
      this.loadPeople();
      this.router.navigateByUrl('/people/add-people');

      this.searchShowState = false;
    } else if (this.state === 'edit') {
      this.searchShowState = false;
      this.router.navigateByUrl('/people/edit-people/' + people_id);
    }else if (this.state === 'search'){
      this.searchShowState = true;
      this.searchCloseState = false;
      this.router.navigate(['people']);
      this.peopleService.changePeopleSearchState();
      this.searchData={};
       this.loadPeople();
    }else if (this.state === 'none'  ||  this.searchShowState) {
      this.searchShowState = true;
      this.router.navigateByUrl('/people');
      this.searchData = {};
      this.loadPeople();
    }
  }

  routeToAssignment(peopleId:any,peopleName:any){
    console.log(peopleId);
    console.log(peopleName);
    this.router.navigate([`/people/assignment/${peopleId}/${peopleName}`]);

  }

  routeToInvoice(peopleId:any,peopleName:any){
    console.log(peopleId);
    console.log(peopleName);
    this.router.navigate([`/people/invoice/${peopleId}/${peopleName}`]);

  }

  routeToExpenses(peopleId: any) {
    this.router.navigate([`people/expenses/${peopleId}`]);
  }

  routeToPayroll(peopleId: any, peopleName: any){
    this.router.navigate([`people/payrolls/${peopleId}/${peopleName}`]);
  }



  ngOnDestroy() {
    this.$peopleSearchSubscription.unsubscribe();
  }
}
