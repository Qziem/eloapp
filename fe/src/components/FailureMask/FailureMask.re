[%bs.raw {|require('./FailureMask.scss')|}];
[@bs.module] external srcNosacz: string = "../../images/nosacz.jpg";

open ReasonReact;

let component = statelessComponent("FailureMask");

let imgNosacz = <img src=srcNosacz />;

let make = _children => {
  ...component,
  render: _self =>
    <div className="FailureMask">
      imgNosacz
      <div className="label">
        {"Kuuuuuuurla, cos sie zrombalo !" |> ReasonReact.string}
      </div>
    </div>,
};