[%bs.raw {|require('./Menu.scss')|}];
open EloTypes;
open BsReactstrap;

let component = ReasonReact.statelessComponent("Menu");

let make = (~activePlace, _children) => {
  ...component,
  render: _self =>
    <Navbar className="menu">
      <Nav pills=true>
        <NavLink active={activePlace == RANK_AND_STATS} href="#rank_and_stats">
          {"Ranking & Stats" |> ReasonReact.string}
        </NavLink>
        <NavLink active={activePlace == OPERATIONS} href="#operations">
          {"Operations" |> ReasonReact.string}
        </NavLink>
      </Nav>
    </Navbar>,
};