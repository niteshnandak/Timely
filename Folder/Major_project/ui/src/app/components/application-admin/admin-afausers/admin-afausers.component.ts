import { animate, state, style, transition, trigger } from '@angular/animations';
import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Component, ElementRef } from '@angular/core';
import { ActivatedRoute, Router, RouterLink, RouterOutlet } from '@angular/router';
import { GridModule } from '@progress/kendo-angular-grid';
import { AdminAfausersService } from '../../../services/admin-services/admin-afausers.service';
import { ToastrService } from 'ngx-toastr';
import { AuthService } from '../../../auth/auth-services/auth.service';
import { LoaderComponent } from '../../loader/loader.component';
import { Title } from '@angular/platform-browser';

@Component({
  selector: 'app-admin-afausers',
  standalone: true,
  imports: [GridModule, CommonModule, RouterOutlet, LoaderComponent, RouterLink],
  templateUrl: './admin-afausers.component.html',
  styleUrl: './admin-afausers.component.css',

  animations:[
    // This is for the search box to occupy the whole page
    trigger('popOverState',[
      state('open', style({
        height: '80vh',
      })),
      transition('* => *',animate('500ms ease')),
    ]),

    //This is for the change of height of the upper row
    trigger('heightControl',[
      state('open',style({
        height: 0,
        opacity: 0,
        display: "none",
      })),
      transition("* => *", animate('500ms ease'))
    ]),

    //this is to change the opacity of a component and to remove from the display
    trigger('opacityControl',[
      state('open',style({
        opacity: 0,
        display: 'none',
      })),
      transition("* => *", animate('500ms ease'))
    ]),

    //This for the display level change in the upper row
    trigger('displayControl', [
      state('open',style({
        display: "none"
      })),
      transition("* => *", animate('100ms ease'))
    ])
  ]

})

//CLASS: AdminAfausersComponent
export class AdminAfausersComponent {
  state: 'none' | 'search' | 'add' | 'edit' = 'none';
  orgId!: number;

  admin: any; // for finding registered user
  isLoading!: boolean;

  organisation_name!: string;
  org_user_count!: number;
  org_active_users!: number;
  org_inactive_users!: number;
  org_active_users_last_24_hours!: number;
  org_new_users_last_month!: number;
  org_users_pending_verification!: number;

  searchFormData: any = null;

  // searchFlag: boolean = false;
  private _searchFlag: boolean = false;

  // Getter for searchFlag
  get searchFlag(): boolean {
    // console.log(this._searchFlag);
    // console.log(this.searchFlag);
    return this._searchFlag;
  }

  // Setter for searchFlag
  set searchFlag(value: boolean) {
    if (this._searchFlag !== value) {
      this._searchFlag = value;
      // console.log(this._searchFlag);
      // console.log(this.searchFlag);
      this.skip = 0; // Reset skip to 0 whenever searchFlag changes
    }
  }

  constructor(
    private adminAfaService: AdminAfausersService,
    private http:HttpClient,
    private router: Router,
    private route: ActivatedRoute,
    private toastr: ToastrService,
    private auth: AuthService,
    private title: Title,
    private elementref: ElementRef
  ) {
    this.title.setTitle('AFA Users');
  }

  ngOnInit(){
    this.isLoading = true;

    this.admin = this.auth.getUser();

    this.orgId = this.route.snapshot.params['orgId'];
    this.adminAfaService.setOrgId(this.orgId);

    //change the grid size according to the sates
    this.adminAfaService.addEvent.subscribe(()=>{
      if(this.state != 'none'){
        this.state = 'none';
        this.searchFormData = null;
        // console.log(this.searchFormData);
        this.loadAfaUsers(this.searchFormData);
      }
    });

    //checks for the state of the search
    this.adminAfaService.searchFormData.subscribe(
      (res: any) => {
        console.log(res);
        if(res){
          this.searchFlag = false; // incase user researched data to paginate from 1st page we set to 0.

          this.searchFormData = res.formData;
          this.searchFlag = res.searchFlag;

          // console.log(this.searchFlag);

          // console.log(this.searchFormData);
          this.loadAfaUsers(this.searchFormData);
      }
    },
    (error: any) => {
      console.log(error.error);
    });

    if(this.state === 'none'){
      this.searchFormData = null;
      this.router.navigateByUrl(`/app-admin/organisation/${this.orgId}/afausers`)
    }

    this.loadAfaUsers(this.searchFormData);
  }


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
    this.loadAfaUsers(this.searchFormData);
  }

  //FUNCTION TO LOAD THE AFA USERS DATA
  loadAfaUsers(searchFormData: any): void {

    this.gridloading = true;

    this.adminAfaService.getAfaUsers(this.orgId, this.skip, this.pageSize, searchFormData).subscribe(
      (response: any) => {
        // console.log(response);
        this.gridData = {
          data: response.searchedUserData,
          total: response.total
        }
        this.gridloading = false;
      },
      (error) => {
        this.toastr.error(error.error.message);
        this.gridloading = false;
      }
    );


    // Fetch live data for cards
    this.adminAfaService.fetchUserStats(this.orgId).subscribe(
      (response: any) => {
        // console.log(response);

        this.organisation_name = response.organisation_name;
        this.org_user_count = response.user_count;
        this.org_active_users = response.active_users;
        this.org_inactive_users = response.inactive_users;
        this.org_active_users_last_24_hours = response.active_users_last_24_hours;
        this.org_new_users_last_month = response.new_users_last_month;
        this.org_users_pending_verification = response.users_pending_verification;

        this.isLoading = false;
      },
      (error) => {
        console.log(error.error);
      }
    );

  }


  deleteUserId:any;
  setDataItem(value:any){
    this.deleteUserId = value;
  }

  // Deleting the AFA User
  deleteUser(user_id: number) {
    this.gridloading= true;
    this.adminAfaService.deleteAfaUser(this.orgId, user_id, this.admin.user_id).subscribe(
      (response: any) => {
        // console.log(response);
        this.toastr.success(response.message);
        this.loadAfaUsers(this.searchFormData);
      },
      (error) => {
        this.toastr.error(error.error.message);
      }
    );

  }

  toggleOrgId:any;
  toggleUserId:any;
  toggleStatus:any;
  setToggleDataItem(organisation_id: number, user_id: number, status: any) {
    this.toggleOrgId = organisation_id;
    this.toggleUserId = user_id;
    this.toggleStatus = status;
  }

  // Toggle the active status
  toggleActiveStatus(organisation_id:any, user_id:any): void { //dataItem: any
    this.gridloading= true;
    this.adminAfaService.toggleActiveStatus(organisation_id, user_id).subscribe(
      (response: any) => {
        this.toastr.success(response.message);
        // dataItem.status = !dataItem.status; // changing its value in frontend
        this.loadAfaUsers(this.searchFormData);
      },
      (error) => {
        this.toastr.error(error.error.message);
      }
    );
  }



  //DYNAMICALLY CHANGE THE STATE OF THE PAGE
  toggleState(value: 'none' | 'search' | 'add' | 'edit', user_id: any) {
    // this.state = this.state === value ? 'none' : value;
    this.state = this.state === value && value != 'edit' ? 'none' : value;

    if(this.state === 'search'){
      setTimeout(() => {
        this.router.navigateByUrl(`/app-admin/organisation/${this.orgId}/afausers/search-afausers`)
      }, 500);

    }else if(this.state === 'add'){
      setTimeout(() => {
        this.searchFormData = null;
        this.searchFlag = false;
        this.loadAfaUsers(this.searchFormData); // for going from seach to add reload data
        // console.log(this.searchFormData);
        this.router.navigateByUrl(`/app-admin/organisation/${this.orgId}/afausers/add-afausers`)
      }, 500);

    }else if(this.state === 'none'){
      this.router.navigateByUrl(`/app-admin/organisation/${this.orgId}/afausers`);
      this.searchFormData = null;
      this.searchFlag = false;
      this.loadAfaUsers(this.searchFormData);
    }

    else if (this.state === 'edit') {
      setTimeout(() => {
        // console.log(this.searchFormData);
        this.router.navigateByUrl(`/app-admin/organisation/${this.orgId}/afausers/edit-afausers/${user_id}`);
      }, 500);


    }
  }






    // //hide or show stats details boolean
    // isOrgsStatShown: boolean = false;

    // //SHOW THE STAT ACCORDING TO ITS STATE
    // toggleShowCard() {
    //   this.isOrgsStatShown = !this.isOrgsStatShown;
    //   if (this.isOrgsStatShown) {
    //     this.elementref.nativeElement.getElementById(
    //       'toggleShowCard'
    //     ).style.display = 'block';
    //   }
    // }








}
