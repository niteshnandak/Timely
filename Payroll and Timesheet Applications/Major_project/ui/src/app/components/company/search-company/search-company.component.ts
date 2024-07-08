import { Component, EventEmitter, Output } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { CompanyService } from '../../../services/company.service';
import { CommonModule } from '@angular/common';
import { composeSortDescriptors } from '@progress/kendo-data-query';
import { CustomersService } from '../../../services/customers.service';

@Component({
  selector: 'app-search-company',
  standalone: true,
  imports: [ReactiveFormsModule, CommonModule],
  templateUrl: './search-company.component.html'
})
export class SearchCompanyComponent {
  @Output() searchResultsEmitted = new EventEmitter<any[]>();
  searchCompanyForm !:FormGroup;
  companyId:string | null = null;
  searchResults: any[] = [];


  constructor(
    private formbuilder : FormBuilder,
    private route:ActivatedRoute,
    private companyService: CompanyService,
    private customerService: CustomersService,
    private router: Router,
  ){}

  ngOnInit(){
    this.searchCompanyForm = this.formbuilder.group({
        company_name:[''],
        email_address:[''],
        status:['']
    })
  }


  searchCompanySubmit(){
    this.companyService.searchCompany(this.searchCompanyForm.value).subscribe((response:any) => {
      this.searchResults = response;
      this.customerService.searchDataChange(this.searchResults);
    })
  }

  resetForm() {
    this.searchCompanyForm.reset();
  }
}
