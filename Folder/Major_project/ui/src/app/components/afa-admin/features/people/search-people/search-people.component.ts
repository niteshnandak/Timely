import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { PeopleService } from '../../../../../services/people.service';
import { AuthService } from '../../../../../auth/auth-services/auth.service';

@Component({
  selector: 'app-search-people',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterLink],
  templateUrl: './search-people.component.html',
})
export class SearchPeopleComponent {
  searchPeopleForm!: FormGroup ;

  people_id:any;

  peopleData:any;
  peopleAddressData:any;
  companies:any;
  user:any;

  constructor(private formbuilder : FormBuilder,
    private route:ActivatedRoute ,
    private peopleService:PeopleService,
    private router:Router,
    private auth:AuthService
  ){}
  ngOnInit(){
    this.user = this.auth.getUser();
    this.searchPeopleForm = this.formbuilder.group({
      peopleSearchDetails: this.formbuilder.group({
        people_name:[null],
        job_title:[null],
        company_name:[null],
        gender:[null],
        joining_date:[null],
      }),
    })
    this.getAllCompanies();

    
    this.peopleService.peopleSearchClickEvent.subscribe((data)=>{
      if(data === true){
       this.searchPeopleForm.reset();
      }
    })
    
    }


    getAllCompanies(){
      this.peopleService.getCompanies().subscribe({
        next: (response)=>{
          this.companies = response.companies
        }
      })
    }


  searchPeopleFormSubmit(){
    console.log(this.searchPeopleForm.value);
    this.peopleService.peopleDataChangeEvent(this.searchPeopleForm.value);
  }

  resetForm() {
    this.searchPeopleForm.reset();
    this.peopleService.changePeopleSearchState();
   
  }

}

