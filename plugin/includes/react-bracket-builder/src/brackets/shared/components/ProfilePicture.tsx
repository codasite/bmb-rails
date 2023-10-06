import React from 'react';
import { ReactComponent as UserIcon } from '../assets/user.svg'


export const ProfilePicture = ({ src, alt, color }: { src: string; alt: string; color?: string }) => {
    const class_name = `tw-h-50 tw-w-50 tw-flex tw-rounded-full tw-border-solid tw-border-1 tw-border-${color} tw-justify-center tw-items-center`;
    const svg_color = `tw-text-${color}`
    return (
        <div className="tw-m-40 tw-flex tw-flex-col tw-justify-center tw-items-center">
            {src ? 
                <img
                    className="tw-h-50 tw-w-50 tw-rounded-full"
                    src={src}
                    alt={alt}
                />
            :
                <div className={class_name}>
                    <UserIcon className={svg_color} />
                </div>
            }
        </div>
    );
};
