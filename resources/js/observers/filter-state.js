import lts from '@/services/lts';
import eventHub from '@/services/event-hub';

eventHub.$on('login', () => {
    delete lts.storedFilters;
});