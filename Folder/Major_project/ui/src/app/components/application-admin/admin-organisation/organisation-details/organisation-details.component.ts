import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import {
  ReactiveFormsModule,
  FormControl,
  FormGroup,
  FormBuilder,
  Validators,
} from '@angular/forms';
import { ToastrService } from 'ngx-toastr';
import { LoaderComponent } from '../../../loader/loader.component';
import { AdminOrganisationsService } from '../../../../services/admin-services/admin-organisations.service';
import { ActivatedRoute, Router, RouterOutlet } from '@angular/router';
import { GridModule } from '@progress/kendo-angular-grid';
@Component({
  selector: 'app-organisation-details',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, GridModule, LoaderComponent],
  templateUrl: './organisation-details.component.html',
  styleUrl: './organisation-details.component.css',
})
export class OrganisationDetailsComponent {
  constructor(
    private orgService: AdminOrganisationsService,
    private route: ActivatedRoute
  ) {}

  orgId: any;
  organisationDetails!: any;
  public isLoading!: boolean ;

  ngOnInit() {
    // this.isLoading = true;
    this.orgId = this.route.snapshot.params['orgId'];
    console.log(this.orgId);
    console.log('i am at the org details page');
    this.orgService.fetchOrgDetails(this.orgId).subscribe((res: any) => {
      this.organisationDetails = res;
    });

  }
}
