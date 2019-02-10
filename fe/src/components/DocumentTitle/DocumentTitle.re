type retainedProps = {title: string};

let component =
  ReasonReact.statelessComponentWithRetainedProps("DocumentTitle");

[@bs.val] external document: Dom.document = "document";
[@bs.set] external setTitleDom: (Dom.document, string) => unit = "title";

[@bs.module "react"]
external fragmentClass: ReasonReact.reactClass = "Fragment";

module Fragment = {
  let make = children =>
    ReasonReact.wrapJsForReason(
      ~reactClass=fragmentClass,
      ~props=Js.Obj.empty(),
      children,
    );
};

let setTitle = setTitleDom(document);
let unsetTitle = () => setTitle("");

let make = (~title: string, children) => {
  ...component,
  retainedProps: {
    title: title,
  },
  didMount: _self => setTitle(title),
  didUpdate: ({oldSelf, newSelf}) =>
    oldSelf.retainedProps.title !== newSelf.retainedProps.title ?
      {
        Js.log(title);
        setTitle(title);
      } :
      (),
  willUnmount: _self => unsetTitle(),
  render: _self => <Fragment> ...children </Fragment>,
};