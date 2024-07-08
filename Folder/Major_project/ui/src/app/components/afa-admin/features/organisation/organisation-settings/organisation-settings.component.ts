import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink, RouterModule } from '@angular/router';
import { AuthService } from '../../../../../auth/auth-services/auth.service';
import { OrganisationService } from '../../../../../services/organisation.service';
import { ToastrService } from 'ngx-toastr';
import { SomeCharsDirective } from '../../../../../directive/some-chars-only/some-chars.directive';
import { NumberOnlyDirective } from '../../../../../directive/number-only/number-only.directive';
import { CharWithSpaceDirective } from '../../../../../directive/char-with-space/char-with-space.directive';
import { LoaderComponent } from '../../../../loader/loader.component';
import { Title } from '@angular/platform-browser';

@Component({
  selector: 'app-organisation-settings',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    CommonModule,
    RouterLink,
    SomeCharsDirective,
    NumberOnlyDirective,
    CharWithSpaceDirective,
    LoaderComponent,
  ],
  templateUrl: './organisation-settings.component.html'
})
export class OrganisationSettingsComponent {

  constructor(
    private fb: FormBuilder,
    private auth: AuthService,
    private orgService: OrganisationService,
    private toastr: ToastrService,
    private router: Router,
    private titleService: Title,

  ){
    this.titleService.setTitle('Settings');
  }


   /**
   * Variables used in this component
   * initialFormData -> to store exisiting org details
   * logoLabel -> text below the Logo
   * fileChosen -> text below the upload button
   * apiUrl -> API endpoint for the logo
   */
   public user !: any;
   public initialFormData !: any;
   public logoLabel : string = "Current Logo";
   public selectedFile !: File;
   public fileChosen: string = 'No file chosen';
   public apiUrl : string = 'http://127.0.0.1:8000/api/org-logo/'
   public imageUrl: string = '';
   public logoSize !: number;
   public isLoading !: boolean;



  /**
   * Get Image URL to display the Logo,
   * Exisiting details of the organisation
   */
  ngOnInit(){
    this.isLoading = true;
    this.user = this.auth.getUser();
    this.orgService.orgInitialInfo(this.user.organisation_id).subscribe((result)=>{
      //set Image URL
      this.imageUrl = this.apiUrl + result.org_logo;

      //patch the values to the reactive form
      this.organisationSettingsForm.patchValue(result);
      this.initialFormData = result;
      this.isLoading = false;
    });
  }

  
  //Organisation Form Builder
  organisationSettingsForm : FormGroup = this.fb.group({
    name: ['', [Validators.required,Validators.maxLength(50),Validators.minLength(3)]],
    email_address: ['', [Validators.required, Validators.pattern('^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$')]],
    contact_number: ['', [Validators.required, Validators.pattern('^[0-9]{10}$')]],
    address_line_1: ['', [Validators.required, Validators.pattern('^[a-zA-z0-9-\/,. ]*$')]],
    address_line_2: ['', Validators.pattern('^[a-zA-z0-9-\/,. ]*$')],
    city:['',[Validators.required, Validators.pattern('^[a-zA-Z ]*$')]],
    state:['',[Validators.required,Validators.pattern('^[a-zA-Z ]*$')]],
    description: [''],
    user_id: ['']
  });


  /**
   * On uploading any Image, function to display that Image in place of exisiting logo
   */
  onFileChange(event: any){
    this.selectedFile = event.target.files[0];
    this.logoSize = this.selectedFile.size / (1000 * 1024);
    console.log(this.selectedFile);
    if (this.selectedFile && this.logoSize < 2 && this.selectedFile.type.startsWith('image')) {
      this.fileChosen = this.selectedFile.name;

      // URL created using Angular's URL interface
      this.imageUrl = URL.createObjectURL(this.selectedFile);
      this.logoLabel = "Uploaded image will be set as Logo";
    } else {
      this.fileChosen = 'No file chosen';
      this.toastr.error("File Format is not valid");
      this.clearFile(event);
    }
  }

  private clearFile(event: any){
    console.log("hjghg");
    const fileInput = document.querySelector('input[type="file"]') as HTMLInputElement;
    if (fileInput) {
      fileInput.value = '';
      this.selectedFile = event.target.files[0];
    }
  }

  /**
   * Upload logo to Backend, and on uploading refresh the component
   */
  uploadLogo(event: any){

    if(!this.selectedFile){
      this.toastr.info('Select an Image to Upload');
    }
    //Create new form data and append image
    const formData = new FormData();
    if(this.logoSize < 2 && this.selectedFile && this.selectedFile.type.startsWith('image')){
      formData.append('file', this.selectedFile, this.selectedFile.name);
      formData.append('org_id',this.user.organisation_id);

      this.isLoading = true;
      this.orgService.uploadLogo(formData).subscribe((result)=>{
        const res: any = result;
        //Redirect the page back to the component.
        this.router.navigateByUrl('/', { skipLocationChange: true }).then(() => {
          this.router.navigate(['/organisation/settings']);

          if(res.message.includes("Logo")){
            this.toastr.success(res.message);
            this.isLoading = false;
          } else {
            this.toastr.error("An error occured");
            this.isLoading = false;
          }
        });
      });
    }
  }

  /**
   * On submitting the Organisation Settings Form function executed
   */
  onSubmit(){
    this.organisationSettingsForm.patchValue({
      user_id: this.user.user_id
    });
    console.log(this.organisationSettingsForm.value);

    this.isLoading = true;
    this.orgService.editOrgDetails(this.organisationSettingsForm.value, this.user.organisation_id).subscribe((result)=>{
      const res : any= result;

      //Redirecting to the current component if edit succesful else displaying the error
      if(res.message.includes("Organisation")){
        this.toastr.success(res.message);
        this.router.navigateByUrl('/', { skipLocationChange: true }).then(() => {
          this.router.navigate(['/organisation/settings']).then(()=>{
            setTimeout(()=>{
              document.getElementById('seperator-2')?.scrollIntoView({behavior: 'smooth'});
            }, 0);
          });
        });
      } else {
        this.toastr.error(res.message);
      }
      this.isLoading = false;
    });
  }


  /**
   * Reset the Form to initial Organisation Info
   */
  onReset(){
    this.organisationSettingsForm.patchValue(this.initialFormData);
  }
}
