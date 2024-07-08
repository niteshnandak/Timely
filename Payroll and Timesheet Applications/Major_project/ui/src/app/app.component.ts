import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { CompanyGridComponent } from './components/company/company-grid/company-grid.component';
import { SideNavbarComponent } from './components/side-navbar/side-navbar.component';

interface sideNavExpand{
  screenWidth: number,
  isOpen: boolean
}

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, CompanyGridComponent,SideNavbarComponent],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent {
  title = 'ui';

  isSideNavOpen = false;
  screenWidth = 0;

  onExpandSidenav(data: sideNavExpand):void{
    this.screenWidth = data.screenWidth;
    this.isSideNavOpen = data.isOpen;
  }
  public gridData: any[] = [
    {
      ProductID: 1,
      ProductName: "Chai",
      UnitPrice: 18,
      Category: {
        CategoryID: 1,
        CategoryName: "Beverages",
      },
    },
    {
      ProductID: 2,
      ProductName: "Chang",
      UnitPrice: 19,
      Category: {
        CategoryID: 1,
        CategoryName: "Beverages",
      },
    },
    {
      ProductID: 3,
      ProductName: "Aniseed Syrup",
      UnitPrice: 10,
      Category: {
        CategoryID: 2,
        CategoryName: "Condiments",
      },
    },
  ];
}
