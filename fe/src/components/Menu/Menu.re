[%bs.raw {|require('./Menu.scss')|}];
let component = ReasonReact.statelessComponent("Menu");

let make = _children => {
  ...component,
  render: _self =>
    <div className="menu">
      <a href="#rank_and_stats"> {"Ranking & Stats" |> ReasonReact.string} </a>
      <a href="#operations"> {"Operations" |> ReasonReact.string} </a>
    </div>,
};