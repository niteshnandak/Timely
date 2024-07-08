import { Component, ViewChild } from '@angular/core';
import { GridModule } from '@progress/kendo-angular-grid';
import { SideNavbarComponent } from '../../side-navbar/side-navbar.component';
import { CommonModule, NgClass } from '@angular/common';
import { CompanyKendoGridComponent } from './company-kendo-grid/company-kendo-grid.component';
import {
  trigger,
  state,
  style,
  animate,
  transition
 } from '@angular/animations';
import { transform } from '@progress/kendo-drawing/dist/npm/geometry';
import { ActivatedRoute, Router, RouterOutlet } from '@angular/router';
import { CompanyService } from '../../../services/company.service';
import { CustomersService } from '../../../services/customers.service';
import { AuthService } from '../../../auth/auth-services/auth.service';
import { LoaderComponent } from '../../loader/loader.component';
import { Title } from '@angular/platform-browser';


@Component({
  selector: 'app-company-grid',
  standalone: true,
  imports: [
    GridModule,
    SideNavbarComponent,
    CommonModule,
    CompanyKendoGridComponent,
    RouterOutlet,
    NgClass,
    LoaderComponent
  ],
  templateUrl: './company-grid.component.html',
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
  ],
})
export class CompanyGridComponent {
  @ViewChild(CompanyKendoGridComponent) gridComponent!: CompanyKendoGridComponent;

  organisation_id:any;
  stats: any = {
    total_companies: 0,
    new_companies_last_week: 0,
    new_companies_last_month: 0,
    active_companies: 0,
    top_companies: []
  };
  public isLoading !: boolean;

  state: 'none' | 'search' | 'add' = 'none';

  constructor(
    private companyService:CompanyService,
    private route: ActivatedRoute,
    private router: Router,
    private customerService: CustomersService,
    private authService: AuthService,
    private title: Title
  ){
    this.title.setTitle('Company')  }

  toggleState(value: 'none' | 'search' | 'add') {
    this.state = this.state === value ? 'none' : value;

    if(this.state === 'search'){
      setTimeout(() => {
        this.router.navigateByUrl('/companies/search-company')
        this.gridComponent.loadItem()
      }, 500);

    }else if(this.state === 'add'){
      setTimeout(() => {
        this.router.navigateByUrl('/companies/add-company')
        this.gridComponent.loadItem()
      }, 500);

    }else if(this.state === 'none'){
      this.router.navigateByUrl('/companies');
      this.customerService.searchBoxSizeChange();
      this.gridComponent.loadItem()
    }
  }



  ngOnInit() {
    this.organisation_id = this.authService.getUser()['organisation_id'];
    this.state = 'none';
    if(this.state === 'none'){
      this.router.navigateByUrl('companies')
    }
    this.customerService.searchEvent.subscribe(()=>{
      this.state = 'none';
    })
    this.loadCompanyStats();
    
  }

  loadCompanyStats(): void {
    // this.isLoading = true;  
    this.companyService.getCompanyStats().subscribe(data => {
      this.stats = data;
      console.log(this.stats)
      // this.isLoading = false;
    }, error => {
      console.error('Error fetching company stats', error);
    });
  }

  onStatusUpdated(): void {
    
    this.loadCompanyStats(); // Update the stats
  }
}
