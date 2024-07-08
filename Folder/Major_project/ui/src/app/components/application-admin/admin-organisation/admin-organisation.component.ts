import { Component, ElementRef, EventEmitter } from '@angular/core';
import { GridModule } from '@progress/kendo-angular-grid';
import { SideNavbarComponent } from '../../side-navbar/side-navbar.component';
import { CommonModule } from '@angular/common';
import {
  trigger,
  state,
  style,
  animate,
  transition,
} from '@angular/animations';
import { transform } from '@progress/kendo-drawing/dist/npm/geometry';
import { ActivatedRoute, Router, RouterOutlet } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { AdminOrganisationsService } from '../../../services/admin-services/admin-organisations.service';
import { ToastrService } from 'ngx-toastr';
import { LoaderComponent } from '../../loader/loader.component';
import { Title } from '@angular/platform-browser';


@Component({
  selector: 'app-admin-organisation',
  standalone: true,
  imports: [GridModule, CommonModule, RouterOutlet, LoaderComponent],
  providers: [AdminOrganisationsService],
  templateUrl: './admin-organisation.component.html',
  styleUrl: './admin-organisation.component.css',
  animations: [
    trigger('popOverState', [
      state('open', style({ height: '80vh' })),
      transition('* => *', animate('500ms ease')),
    ]),
    trigger('heightControl', [
      state('open', style({ height: 0, opacity: 0, display: 'none' })),
      transition('* => *', animate('500ms ease')),
    ]),
    trigger('displayControl', [
      state('open', style({ display: 'none' })),
      transition('* => *', animate('100ms ease')),
    ]),
  ],
})

//CLASS: AdminOrganisationComponent
export class AdminOrganisationComponent {
  //common loader variable
  public isLoading!: boolean;
  //hide or show stats details boolean
  isOrgsStatShown: boolean = false;
  state: 'none' | 'search' | 'add' | 'edit' = 'none';
  org_active_count: any;
  org_inactive_count: any;
  org_total_count: any;
  org_week_count: any;
  org_month_count: any;
  searchFormData: any = null;
  private _searchFlag: boolean = false;

  //used to paginate to 1 if search results were found
  get searchFlag(): boolean {
    return this._searchFlag;
  }

  // Setter for searchFlag
  set searchFlag(value: boolean) {
    if (this._searchFlag !== value) {
      this._searchFlag = value;
      // reset skip to 0 whenever searchFlag changes
      this.skip = 0;
    }
  }

  constructor(
    private router: Router,
    private toastr: ToastrService,
    private orgService: AdminOrganisationsService,
    private route: ActivatedRoute,
    private elementref: ElementRef,
    private titleService: Title,

  ) {
    this.titleService.setTitle('Organisations');
  }

  ngOnInit() {
    this.state = 'none';

    if (this.state === 'none') {
      this.router.navigateByUrl('app-admin/organisation');
    }

    //change the grid size according to the sates
    this.orgService.addEvent.subscribe(() => {
      this.state = 'none';
      this.searchFormData = null;
      this.loadOrganisations(this.searchFormData);
    });

    //checks for the state of the search
    this.orgService.searchFormData.subscribe((res: any) => {
      if (res) {
        this.searchFlag = false;
        //stores if any search data found or else null
        this.searchFormData = res.formData;
        this.searchFlag = res.searchFlag;
        this.loadOrganisations(this.searchFormData);
      }
    });
  }

  //kendo-grid details
  gridloading = false;
  public gridData: any = { data: [], total: 0 };
  pageSize = 10;
  skip = 0;
  total = 0;

  //FUNCTION IF PAGE CHANGE
  pageChange(event: any) {
    //update kendo page change skip and pageSize
    this.skip = event.skip;
    this.pageSize = event.take;
    this.loadOrganisations(this.searchFormData);
  }

  //FUNCTION TO LOAD THE ORG DATA
  loadOrganisations(searchFormData: any): void {
    this.gridloading = true;
    this.orgService
      .getAllOrganisations(this.skip, this.pageSize, searchFormData)
      .subscribe(
        (res: any) => {
          //set the total data and its count
          this.gridData = {
            data: res.data.orgs_data,
            total: res.data.total_org_count,
          };
          this.gridloading = false;
        },
        (error: any) => {
          console.log(error);
          this.toastr.error(error.error.message, 'Failed');
        }
      );

    //update the stats based on the grid data dynamically
    this.orgService.fetchOrgStats().subscribe(
      (res: any) => {
        this.org_active_count = res.org_active;
        this.org_inactive_count = res.org_inactive;
        this.org_total_count = res.org_total;
        this.org_week_count = res.org_last_week;
        this.org_month_count = res.org_last_month;
      },
      (error: any) => {
        this.toastr.error('Failed to fetch the organisations Stats.', 'Failed');
      }
    );
  }

  //SHOW THE STAT ACCORDING TO ITS STATE
  toggleShowCard() {
    this.isOrgsStatShown = !this.isOrgsStatShown;
    if (this.isOrgsStatShown) {
      this.elementref.nativeElement.getElementById(
        'toggleShowCard'
      ).style.display = 'block';
    }
  }

  //FUNCTION TO NAVIGAE TO EDIT HTTP LINK
  editOrganisationData(orgId: Number) {
    this.router.navigateByUrl(
      '/app-admin/organisation/edit-organisation/' + orgId
    );
  }

  //STORE ID TO USE FOR THE MODEL
  deleteOrgId: any;
  setDataItem(value: any) {
    this.deleteOrgId = value;
  }

  orgDetailsInfo(orgId: any) {
    this.router.navigate([`/app-admin/organisation/${orgId}/details`]);
  }

  //FUNCTION TO DELETE ORGANISATION
  deleteOrganisation(orgId: any) {
    this.orgService.deleteOrgDetails(orgId).subscribe(
      //response
      (response: any) => {
        console.log(response);
        this.toastr.success(response.message, 'Success');
        //reload after successfull deletion
        this.loadOrganisations(this.searchFormData);
      },

      //error
      (error) => {
        console.log(error);
        this.toastr.error(error.error.message, 'Failed');
      }
    );
  }

  //DYNAMICALLY CHANGE THE STATE OF THE PAGE
  toggleState(value: 'none' | 'search' | 'add' | 'edit') {
    this.state = this.state === value ? 'none' : value;

    //if the state is search navigae accordingly
    if (this.state === 'search') {
      setTimeout(() => {
        this.router.navigateByUrl(
          '/app-admin/organisation/search-organisation'
        );
      }, 500);
    }
    //if the state is add
    else if (this.state === 'add') {
      setTimeout(() => {
        this.searchFormData = null;
        this.searchFlag = false;

        this.router.navigateByUrl('/app-admin/organisation/add-organisation');
      }, 500);
    }
    //if its state is none
    else if (this.state === 'none') {
      this.searchFormData = null;
      this.searchFlag = false;

      this.router.navigateByUrl('/app-admin/organisation');
      this.loadOrganisations(this.searchFormData);
    }
  }

  //DYNAMICALLY CHANGE THE STATUS OF THE ORGANISATION
  toggleActiveStatus(dataItem: any) {
    this.isLoading = true;
    this.orgService.toggleActiveStatus(dataItem.organisation_id).subscribe(
      //response
      (response: any) => {
        //successfull toggled status
        this.toastr.success(response.message);
        //change the status to vice versa
        dataItem.status = !dataItem.status;
        this.loadOrganisations(this.searchFormData);
        this.isLoading = false;

        //error
        (error: any) => {
          this.toastr.error(error.error.message);
        };
      }
    );
  }

  //ROUTE TO USERS PAGE
  viewUsers(orgId: any) {
    this.router.navigate([`/app-admin/organisation/${orgId}/afausers`]);
  }
}
