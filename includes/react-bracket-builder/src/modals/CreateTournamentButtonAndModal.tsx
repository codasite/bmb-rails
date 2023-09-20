import {ReactComponent as SignalIcon} from "../brackets/shared/assets/signal.svg";
import {ReactComponent as FileIcon} from "../brackets/shared/assets/file.svg";
import {ReactComponent as PlusIcon} from "../brackets/shared/assets/plus.svg";
import * as React from 'react';
import {useState} from 'react';

export const CreateTournamentButtonAndModal = (props: {
  myTemplatesUrl: string,
  bracketTemplateBuilderUrl: string
}) => {
  const [show, setShow] = useState(false);
  return <>
    <button
      className="tw-border-solid tw-border tw-border-white tw-bg-white/15 tw-flex tw-gap-16 tw-items-center tw-justify-center tw-rounded-8 tw-p-16 hover:tw-bg-white hover:tw-text-black tw-font-sans tw-text-white tw-uppercase tw-w-full tw-cursor-pointer"
      onClick={() => setShow(true)}>
      <SignalIcon/>
      <span className="tw-font-700 tw-text-24 ">Create Tournament</span>
    </button>
    {show && <div onClick={() => setShow(false)}
                  tabIndex={-1}
                  className="tw-fixed tw-bg-black tw-bg-opacity-50 tw-top-0 tw-left-0 tw-right-0 tw-z-50 tw-w-full tw-p-4 tw-overflow-x-hidden tw-overflow-y-auto md:tw-inset-0 tw-h-[calc(100%-1rem)] tw-max-h-full tw-justify-center tw-items-center tw-flex">
      <div onClick={(e) => e.stopPropagation()} className="tw-relative tw-max-w-[606px] tw-max-h-full tw-p-60 tw-rounded-16 tw-bg-dark-blue">
        <h1 className="tw-text-32 tw-leading-10 tw-text-center tw-font-white tw-whitespace-pre-line tw-mb-50">{`Host a tournament.
 invite & compete with friends.`}</h1>
        <a href={props.myTemplatesUrl}
           className="tw-border-solid tw-border tw-border-green tw-bg-green/20 tw-flex tw-gap-16 tw-items-center tw-justify-center tw-rounded-8 md:tw-p-40 hover:tw-text-white/75 tw-font-sans tw-text-white tw-uppercase tw-w-full tw-text-20 tw-font-500 tw-mb-15 tw-p-20">
          <FileIcon/><span>Use a template</span>
        </a>
        <a href={props.bracketTemplateBuilderUrl}
           className="tw-border-solid tw-border tw-border-white tw-bg-white/20 tw-flex tw-gap-16 tw-items-center tw-justify-center tw-rounded-8 md:tw-p-40 hover:tw-text-white/75 tw-font-sans tw-text-white tw-uppercase tw-w-full tw-text-20 tw-font-500 tw-mb-15 tw-p-20">
          <PlusIcon/><span>Start from scratch</span>
        </a>
        <button
          onClick={() => setShow(false)}
          className="tw-bg-white/15 tw-flex tw-gap-16 tw-items-center tw-justify-center tw-rounded-8 tw-p-12 tw-border-none hover:tw-text-white/75 tw-font-sans tw-text-white tw-uppercase tw-w-full tw-text-16 tw-font-500 tw-cursor-pointer">
          Cancel
        </button>
      </div>
    </div>}
  </>
}
