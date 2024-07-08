import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SearchOrganisationComponent } from './search-organisation.component';

describe('SearchOrganisationComponent', () => {
  let component: SearchOrganisationComponent;
  let fixture: ComponentFixture<SearchOrganisationComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [SearchOrganisationComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(SearchOrganisationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
