import { CommonModule } from '@angular/common';
import { Component, ElementRef, EventEmitter, Output } from '@angular/core';
import { RouterLink } from '@angular/router';
import { LineChartComponent } from '../line-chart/line-chart.component';
import { StackBarChartComponent } from '../stack-bar-chart/stack-bar-chart.component';
import { Popover }from 'bootstrap';
import { AuthService } from '../../../../../auth/auth-services/auth.service';
import { OrganisationService } from '../../../../../services/organisation.service';
import { LoaderComponent } from '../../../../loader/loader.component';
import { Title } from '@angular/platform-browser';
@Component({
  selector: 'app-organisation-dashboard',
  standalone: true,
  imports: [
    CommonModule,
    RouterLink,
    LineChartComponent,
    StackBarChartComponent,
    LoaderComponent
  ],
  templateUrl: './organisation-dashboard.component.html',
})
export class OrganisationDashboardComponent {

  constructor(
    private elementRef: ElementRef,
    private auth: AuthService,
    private orgService: OrganisationService,
    private titleService: Title
  ){
    this.titleService.setTitle('Dashboard');
  }

  /**Variables used in this component
   * chart1data -> line-chart data
   * chart2data -> stack-chart data
   * apiUrl -> organisation logo end point
   * imageUrl -> logo URL in db
   * statistics -> stats displayed in the dashboard
   * user -> User details of the Login
   */
  

  chart1Data !: number[];
  chart2Data !: any;
  orgLogoName !: string;
  public apiUrl : string = 'http://127.0.0.1:8000/api/org-logo/';
  public imageUrl = '';
  public userName : string = "";
  public orgName : string = "";
  public statistics !: any[];
  public isLoading !: boolean;
  user: any;

  /**
   * Dashboard data fetch OnInit from API endpoint
   * ImageURL and data for Graphs set in this function
   */
  ngOnInit(){
    this.isLoading = true;
    this.user = this.auth.getUser();

    this.orgService.dashboardData(this.user.user_id).subscribe((result)=>{
      this.userName = result.user_name;
      this.orgName = result.org_name;
      this.statistics = [
        { label: 'Companies', value: result.total_companies, color: '#8B4513' },
        { label: 'Assignments', value: result.total_assignments, color: '#228B22' }
      ]; 
      this.chart1Data = result.lineChartData;
      this.chart2Data = result.stackChartData;
      this.imageUrl = this.apiUrl + result.org_logo;
      this.isLoading = false;
    });
  }

  /**
   * After ViewInit, adding popovers on hover to each popover-icon
   */

  ngAfterViewInit(): void {
    const popoverIcons = this.elementRef.nativeElement.querySelectorAll('.popover-icon');
    popoverIcons.forEach((icon: HTMLElement, index: number) => {
      icon.addEventListener('mouseenter', () => {
        const popoverContent = this.getPopoverContent(index);
        this.showPopover(icon, popoverContent);
      });

      icon.addEventListener('mouseleave', (event) => {
        event.stopPropagation(); 
        const existingPopovers = document.querySelectorAll('.popover.show'); 
        existingPopovers.forEach((popover) => {
          popover.remove(); 
        });
      });
    });
  }

  /**
   * PopOver Template and Placement defined here
   */
  showPopover(element: HTMLElement, content: string) {
    if (typeof Popover !== 'undefined') {
      new Popover(element, {
        placement: 'right',
        trigger: 'manual', 
        content: content,
        template: `<div class="popover custom-popover d-flex flex-row align-items-center" role="tooltip">
                    <div class="popover-arrow"></div>
                    <div class="popover-body"></div>
                  </div>`
      }).show();
    }
  }

  /**
   * Content for the Popover Set here based on Index 
   */

  getPopoverContent(index: number): string {
    switch (index) {
      case 0:
        return 'Displays no. of assignments per month by all companies in this year';
      case 1:
        return `No. of companies and assignments under ${this.orgName}`;
      default:
        return 'Displays distribution of assignment status of top 5 companies';
    }
  }
}
